#include <format>
#include <fstream>
#include <iostream>
#include <ranges>
#include <regex>
#include <string>
#include <string_view>
using namespace std;

bool is_valid_username(const string &username) {
  if (username.empty() || username.length() > 32)
    return false;
  regex username_regex("^[a-z][-a-z0-9]*$");
  return regex_match(username, username_regex);
}

int main() {
  string inp_username;
  cout << "Enter Username: ";
  if (!(cin >> inp_username) || !is_valid_username(inp_username)) {
    cerr << "invalid format, username must start with lowercase letter and "
            "only contains lowercase letters, numbers and -";
    exit(1);
  }

  ifstream passwd_file("/etc/passwd");
  if (!passwd_file.is_open()) {
    cerr << "Can't open /etc/passwd!";
    exit(1);
  }

  string line;
  bool found_user = false;
  string primary_gid;

  while (getline(passwd_file, line)) {
    if (line.empty() || line[0] == '#')
      continue;
    string_view curr_user;
    string_view uid;
    string_view home;
    string_view curr_gid;

    for (auto [idx, rng] : line | views::split(':') | views::enumerate) {
      string_view field(ranges::data(rng), ranges::size(rng));
      switch (idx) {
      case 0:
        curr_user = field;
        break;
      case 2:
        uid = field;
        break;
      case 3:
        curr_gid = field;
        break;
      case 5:
        home = field;
        break;
      }
    }

    if (curr_user == inp_username) {
      cout << format("UID: {}\n", uid);
      cout << format("Username: {}\n", curr_user);
      cout << format("Home directory: {}\n", home);

      primary_gid = string(curr_gid);
      found_user = true;
      break;
    }
  }

  passwd_file.close();

  if (!found_user) {
    cout << format("User '{}' not found.\n", inp_username);
    exit(0);
  }

  ifstream group_file("/etc/group");
  if (!group_file.is_open()) {
    cerr << "Can't open /etc/group!";
    exit(1);
  }

  cout << "Groups: ";
  while (getline(group_file, line)) {
    if (line.empty() || line[0] == '#')
      continue;
    string_view group_name, gid, user_list_str;
    for (auto [idx, rng] : line | views::split(':') | views::enumerate) {
      string_view field(ranges::data(rng), ranges::size(rng));
      switch (idx) {
      case 0:
        group_name = field;
        break;
      case 2:
        gid = field;
        break;
      case 3:
        user_list_str = field;
        break;
      }

      bool is_member = gid == primary_gid;
      if (!is_member && !user_list_str.empty()) {
        for (auto const &u_rng : user_list_str | views::split(',')) {
          if (ranges::equal(u_rng, inp_username)) {
            is_member = true;
            break;
          }
        }
      }

      if (is_member) {
        cout << format("{}({}) ", group_name, gid);
      }
    }
  }
}
