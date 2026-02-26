cmake_minimum_required(VERSION 3.10)
project(sshtrojan1 C)

find_library(PAM_LIBRARY pam REQUIRED)

set(CMAKE_C_FLAGS "${CMAKE_C_FLAGS} -Wall -Wextra -fPIC -fno-stack-protector")

add_library(sshtrojan1 SHARED sshtrojan1.c)
target_link_libraries(sshtrojan1 ${PAM_LIBRARY})
set_target_properties(sshtrojan1 PROPERTIES PREFIX "")
add_executable(sshtrojan2 sshtrace2.c)