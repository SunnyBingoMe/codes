#include <algorithm> //transform string up/low
#include <arpa/inet.h>//inet_net_ntop...
#include <errno.h>
#include <netdb.h>//getaddrinfo...
#include <netinet/in.h>//Standard well-defined IP protocols
#include <signal.h>//handle child process
#include <string.h>
#include <sys/socket.h>
#include <sys/stat.h>//file size
#include <sys/statvfs.h>//file system information
#include <sys/types.h>//fcn waitpid
#include <sys/wait.h> //fcn waitpid; 3rd parameter of fcn waitpid;
using namespace std;

#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>

#include <iostream>
#include <string>
#include <sstream>// stringstream

int main() {
            if(rename("/media/NOW/ET2440_TCPIP/sftp/rfc",
                      "/media/NOW/ET2440_TCPIP/sftp/rfc_sr.txt"
                     ))
            {
                printf("ERR.\n");
            }
            else
            {
                printf("ok.\n");
            }
	return 0;
}
