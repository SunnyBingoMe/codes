/*
** server.c -- a stream socket server demo
*/

#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <errno.h>
#include <string.h>
#include <sys/types.h>//fcn waitpid
#include <sys/socket.h>
#include <netinet/in.h>
#include <netdb.h>
#include <arpa/inet.h>
#include <sys/wait.h> //fcn waitpid; 3rd parameter of fcn waitpid;
#include <signal.h>
#include <time.h>

#define PORT "13"  // the port users will be connecting to
#define BACKLOG 10	 // how many pending connections queue will hold

#define DEBUG

void sigchld_handler(int s){

	while(waitpid(-1, NULL, WNOHANG) > 0);//http://linux.die.net/man/2/wait; http://www.mkssoftware.com/docs/man3/waitpid.3.asp
    #ifdef DEBUG
        printf("in sigchld_handler()\n");
    #endif

    printf("SERVER: waiting for connections...\n\n");

}

// get sockaddr, IPv4 or IPv6:
void *get_in_addr(struct sockaddr *sa){

    #ifdef DEBUG
        printf("in get_in_addr()\n");
    #endif

	if (sa->sa_family == AF_INET) {
	    #ifdef DEBUG
            printf("    sa->sa_family == AF_INET\n");
		#endif
		return &(((struct sockaddr_in*)sa)->sin_addr);
	}
	#ifdef DEBUG
       printf("    sa->sa_family != AF_INET\n");
	#endif
	return &(((struct sockaddr_in6*)sa)->sin6_addr);
}

char temp[5];//5 = num digits in 32676
char ans[5];
char * int2str(int num){
int i=0,j=0,k=0;
int ones=0;

while (num!=0){
    ones=num%10;//get current ones digit
    temp[i]=(char)(ones+48);//48=(int)'0';
    num=num/10;//remove current ones digit
    i++;//length of number
}
for(j=i-1;j>=0;j--){
ans[k]=temp[j]; //reorder string correctly
k++;
}
ans[i]='\0'; //add null char for end of string
return (char *)ans;
}


int main(void){

	int sockfd, new_fd;  // listen on sock_fd, new connection on new_fd
	struct addrinfo hints, *servinfo, *p;
	struct sockaddr_storage their_addr; // connector's address information
	socklen_t sin_size;//unsigned int
	struct sigaction sa;
	/*(Also, this whole sigaction() thing might be new to you --
	that's ok. The code that's there is responsible for
	reaping zombie processes that appear as the
	fork()ed child processes exit.
	If you make lots of zombies and don't reap them,
	your system administrator will become agitated.)
	*/
	int yes=1;
	char s[INET6_ADDRSTRLEN],msg[23];
	char * p_msg,p_ans;
	int rv;
    time_t rawtime;
    struct tm * p_timeinfo;
    char * mon[]={"   ",
                  "JAN",
                  "FEB",
                  "MAR",
                  "APR",
                  "MAY",
                  "JUN",
                  "JUL",
                  "OUG",
                  "SEP",
                  "OCT",
                  "NOV",
                  "DEC"
                  };
	memset(&hints, 0, sizeof hints);
	hints.ai_family = AF_UNSPEC;
	hints.ai_socktype = SOCK_STREAM;
	hints.ai_flags = AI_PASSIVE; // use my IP
    #ifdef DEBUG
       printf("complete ini \n");
	#endif
	if ((rv = getaddrinfo(NULL, PORT, &hints, &servinfo)) != 0) {
		fprintf(stderr, "getaddrinfo: %s\n", gai_strerror(rv));
		return 1;
	}
    #ifdef DEBUG
    else
       printf("complete getaddrinfo \n");
	#endif

	// loop through all the results and bind to the first we can
	for(p = servinfo; p != NULL; p = p->ai_next) {
		if ((sockfd = socket(p->ai_family, p->ai_socktype,
				p->ai_protocol)) == -1) {
			perror("server: socket");
			continue;
		}
		#ifdef DEBUG
		else
            printf("complete socket() \n");
        #endif

		if (setsockopt(sockfd,
                       SOL_SOCKET,
                       SO_REUSEADDR,
                       &yes,/*#define SOL_SOCKET	1; #define SO_REUSEADDR	2*/
                       sizeof(int)
                       )== -1) {//why should it be reusable?
			perror("setsockopt");
			exit(1);
		}
        #ifdef DEBUG
        else
            printf("complete setsockopt \n");
        #endif

		if (bind(sockfd, p->ai_addr, p->ai_addrlen) == -1) {
			close(sockfd);
			perror("server: bind");
			continue;
		}
        #ifdef DEBUG
        else
            printf("complete bind \n");
        #endif

		break;
	}
    #ifdef DEBUG
        printf("complete for()_p=servinfo \n");
    #endif

	if (p == NULL)  {
		fprintf(stderr, "82. server: failed to bind. p == NULL\n");
		return 2;
	}
    #ifdef DEBUG
    else
        printf("complete p!=NULL \n");
    #endif

	freeaddrinfo(servinfo);
	// all done with this structure //why use only one but clear all?

	if (listen(sockfd, BACKLOG) == -1) {
		perror("89. listen");
		exit(1);
	}
    #ifdef DEBUG
    else
        printf("complete listen\n");
    #endif

	sa.sa_handler = sigchld_handler; //reap all dead processes
	sigemptyset(&sa.sa_mask);
	sa.sa_flags = SA_RESTART;
	if (sigaction(SIGCHLD,
                  &sa,/*struct sigaction sa*/
                  NULL
                  ) == -1){
		perror("sigaction");
		exit(1);
	}
    #ifdef DEBUG
    else
        printf("complete sigaction\n");
    #endif

	printf("server: waiting for connections...\n\n");

	while(1) {  // main accept() loop

//	    printf("SERVER: waiting for connections...\n\n");

		sin_size = sizeof their_addr;
		new_fd = accept(sockfd, (struct sockaddr *)&their_addr, &sin_size);
		if (new_fd == -1) {
			perror("accept");
			continue;
		}
		#ifdef DEBUG
        else
            printf("complete accept\n");
        #endif

		inet_ntop(their_addr.ss_family,
                  get_in_addr((struct sockaddr *)&their_addr),
                  s, sizeof s);
		printf("SERVER: got connection from %s\n", s);//char s[INET6_ADDRSTRLEN];

		if (!fork()) { // this is the child process.
		    /*success, fork() return 0 to child and
		    return child's PID to parent
		    */
		    #ifdef DEBUG
                printf("complete child fork()\n");
            #endif

			close(sockfd); // child doesn't need the listener
		    #ifdef DEBUG
                printf("complete child close(sockfd)\n");
            #endif


            time ( &rawtime );
            p_timeinfo = localtime ( &rawtime );
		    #ifdef DEBUG
                printf("complete child p_timeinfo\n");
            #endif

            //day
            p_ans=int2str(p_timeinfo->tm_mday);
            if(p_timeinfo->tm_mday<10){
                msg[0]='0';
                msg[1]=ans[0];
            }
            else{
                msg[0]=ans[0];
                msg[1]=ans[1];
            }

            //month
            msg[3]=mon[p_timeinfo->tm_mon][0];
            msg[4]=mon[p_timeinfo->tm_mon][1];
            msg[5]=mon[p_timeinfo->tm_mon][2];

            //year
            p_ans=int2str(p_timeinfo->tm_year);
            if(temp[1]<10){
                msg[7]='0';
                msg[8]=temp[0];
            }
            else{
                msg[7]=temp[1];
                msg[8]=temp[0];
            }

            //hour
            p_ans=int2str(p_timeinfo->tm_hour);
            if(p_timeinfo->tm_hour<10){
                msg[10]='0';
                msg[11]=ans[0];
            }
            else{
                msg[10]=ans[0];
                msg[11]=ans[1];
            }

            //minute
            p_ans=int2str(p_timeinfo->tm_min);
            if(p_timeinfo->tm_min<10){
                msg[13]='0';
                msg[14]=ans[0];
            }
            else{
                msg[13]=ans[0];
                msg[14]=ans[1];
            }

            //second
            p_ans=int2str(p_timeinfo->tm_sec);
            if(p_timeinfo->tm_sec<10){
                msg[16]='0';
                msg[17]=ans[0];
            }
            else{
                msg[16]=ans[0];
                msg[17]=ans[1];
            }

            //charactistics
            msg[2]=msg[6]=msg[9]=msg[18]=' ';//spaces
            msg[12]=msg[15]=':';//solons
            msg[19]='C';
            msg[20]='E';
            msg[21]='S';
            msg[22]='T';
            //Central European Summer Time
            p_msg = msg;
			if ((send(new_fd, p_msg, sizeof(msg), 0)) == -1)
				perror("send");
            #ifdef DEBUG
                printf("complete child send()\n");
                printf("complete child send(): %s\n",p_msg);
            #endif

			close(new_fd);
			#ifdef DEBUG
                printf("complete child close(new_fd)\n");
            #endif

			exit(0);
		}

		close(new_fd);  // parent doesn't need this
		#ifdef DEBUG
            printf("complete parent close(new_fd)\n");
        #endif
	}//while(1)

	return 0;
}

