#include <crypt.h>
#include <fstream>
#include <iostream>
#include <random>
#include <ranges>
#include <stdexcept>
#include <termios.h>
#include <unistd.h>

using namespace std;

class TerminalEchoGuard {
private:
  termios old_t{};
  bool is_disabled{false};

public:
  TerminalEchoGuard() {
    if (tcgetattr(STDIN_FILENO, &old_t) == 0) {
      termios new_t = old_t;
      new_t.c_lflag &= ~ECHO;
      tcsetattr(STDIN_FILENO, TCSANOW, &new_t);
      is_disabled = true;
    }
  }

  ~TerminalEchoGuard() {
    if (is_disabled) {
      tcsetattr(STDIN_FILENO, TCSANOW, &old_t);
    }
  }

  static string prompt(const string &message) {
    cout << message << flush;
    TerminalEchoGuard guard;
    string input;
    getline(cin, input);
    cout << '\n';
    return input;
  }
};

class CryptoUtils {
public:
  static bool verify(const string &input, const string &hash) {
    char *computed = crypt(input.c_str(), hash.c_str());
    if (!computed)
      throw runtime_error("Hash failed!");
    return hash == computed;
  }

  static string generate_hash(const string &new_password) {
    const std::string_view charset =
        "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789./";
    std::random_device rd;
    std::mt19937 gen(rd());
    std::uniform_int_distribution<size_t> dist(0, charset.size() - 1);

    std::string salt_body;
    for (int i = 0; i < 16; ++i) {
      salt_body += charset[dist(gen)];
    }

    std::string full_salt = "$6$" + salt_body + "$";
    char *new_hash = crypt(new_password.c_str(), full_salt.c_str());
    if (!new_hash)
      throw std::runtime_error("Hash generation failed.");

    return std::string(new_hash);
  }
};

class SystemParsers {
public:
  static string get_username_from_uid(uint32_t target_uid) {
    ifstream in("/etc/passwd");
    if (!in.is_open())
      throw runtime_error("Cannot open /etc/passwd");
    string line;
    while (getline(in, line)) {
      if (line.empty() || line[0] == '#')
        continue;
      auto fields = line | views::split(':');
      auto it = fields.begin();

      // Field 1: username
      //
      if (it == fields.end())
        continue;
      auto user_rng = *it;
      string username = string(user_rng.data(), user_rng.size());
      if (++it == fields.end())
        continue;
      // Field 2: password hash (ignored)
      if (++it == fields.end())
        continue;
      // Field 3: UID
      auto uid_rng = *it;
      string uid_str = string(uid_rng.data(), uid_rng.size());
      try {
        uint32_t uid = stoul(uid_str);
        if (uid == target_uid) {
          return username;
        }
      } catch (...) {
        continue;
      }
    }

    throw runtime_error("User with UID " + to_string(target_uid) +
                        " not found");
  }

  static string get_password_hash(const string &username) {
    ifstream in("/etc/shadow");
    if (!in.is_open())
      throw runtime_error("Cannot open /etc/shadow");
    string line;
    while (getline(in, line)) {
      if (line.empty() || line[0] == '#')
        continue;
      auto fields = line | views::split(':');
      auto it = fields.begin();

      // Field 1: username
      //
      if (it == fields.end())
        continue;
      auto user_rng = *it;
      string user_in_file = string(user_rng.data(), user_rng.size());
      if (user_in_file != username)
        continue;

      if (++it == fields.end())
        throw runtime_error("Malformed /etc/shadow entry for " + username);
      // Field 2: password hash
      auto hash_rng = *it;
      return string(hash_rng.data(), hash_rng.size());
    }

    throw runtime_error("User " + username + " not found in /etc/shadow");
  }

  static void update_password_hash(const string &username,
                                   const string &new_hash) {
    ifstream in("/etc/shadow");
    if (!in.is_open())
      throw runtime_error("Cannot open /etc/shadow");
    string line;
    string file_content;
    string today = to_string(time(nullptr) / (24 * 3600));
    bool updated = false;

    while (getline(in, line)) {
      if (line.empty() || line[0] == '#') {
        file_content += line + '\n';
        continue;
      }
      auto fields = line | std::views::split(':');
      auto it = fields.begin();

      // Field 1: username
      //
      if (it == fields.end()) {
        file_content += line + '\n';
        continue;
      }
      auto user_rng = *it;
      string user_in_file = string(user_rng.data(), user_rng.size());
      if (user_in_file != username) {
        file_content += line + '\n';
        continue;
      }

      if (++it == fields.end())
        throw runtime_error("Malformed /etc/shadow entry for " + username);
      // Field 2: password hash
      file_content += username + ':' + new_hash + ':';

      // Field 3: modification date
      file_content += today + ':';

      for (++it; it != fields.end(); ++it) {
        auto field_rng = *it;
        file_content += string(field_rng.data(), field_rng.size()) + ':';
      }
      file_content.back() = '\n'; // Replace last ':' with '\n'
      updated = true;
    }

    in.close();

    if (!updated)
      throw runtime_error("User " + username + " not found in /etc/shadow");

    ofstream out("/etc/shadow", ios::trunc);

    if (!out.is_open())
      throw runtime_error("Cannot open /etc/shadow for writing");
    out << file_content;
  }
};

int main() {
  if (geteuid() != 0) {
    cerr << "This program must be run with EUID 0 (root)!" << endl;
    return 1;
  }
  try {
    uint32_t uid = getuid();
    string username = SystemParsers::get_username_from_uid(uid);
    string current_hash = SystemParsers::get_password_hash(username);

    string current_password = TerminalEchoGuard::prompt("Current password: ");
    if (!CryptoUtils::verify(current_password, current_hash)) {
      cerr << "Incorrect password!" << endl;
      return 1;
    }

    string new_password = TerminalEchoGuard::prompt("New password: ");
    string confirm_password =
        TerminalEchoGuard::prompt("Confirm new password: ");

    if (new_password != confirm_password) {
      cerr << "Passwords do not match!" << endl;
      return 1;
    }

    string new_hash = CryptoUtils::generate_hash(new_password);
    SystemParsers::update_password_hash(username, new_hash);
    cout << "Password updated successfully!" << endl;
  } catch (const exception &e) {
    cerr << "Error: " << e.what() << endl;
    return 1;
  }
  return 0;
}
