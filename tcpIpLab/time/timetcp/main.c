#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <errno.h>
#include <string.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <netdb.h>
#include <arpa/inet.h>
#include <sys/wait.h>
#include <signal.h>
#include <time.h>

#define PORT "37"
#define BACKLOG 10

//#define DEBUG

void sigchld_handler(int s)
{

	while(waitpid(-1, NULL, WNOHANG) > 0);
    #ifdef DEBUG
        printf("in sigchld_handler()\n");
    #endif

    printf("SERVER: waiting for connections...\n\n");

}

void *get_in_addr(struct sockaddr *sa)
{

    #ifdef DEBUG
        printf("in get_in_addr()\n");
    #endif

	if (sa->sa_family == AF_INET)
	{
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


int main(void)
{

	int sockfd, new_fd;  // listen on sock_fd, new connection on new_fd
	struct addrinfo hints, *servinfo, *p;
	struct sockaddr_storage their_addr; // connector's address information
	socklen_t sin_size;//unsigned int
	struct sigaction sa;
	int yes=1;
	char s[INET6_ADDRSTRLEN];
	char * p_msg;//,p_ans;
	int rv;
	unsigned int * p_intmsg;

    time_t rawtime;


	memset(&hints, 0, sizeof hints);
	hints.ai_family = AF_UNSPEC;
	hints.ai_socktype = SOCK_STREAM;
	hints.ai_flags = AI_PASSIVE; // use my IP
    #ifdef DEBUG
       printf("complete ini \n");
	#endif
	if ((rv = getaddrinfo(NULL, PORT, &hints, &servinfo)) != 0)
	{
		fprintf(stderr, "getaddrinfo: %s\n", gai_strerror(rv));
		return 1;
	}
    #ifdef DEBUG
    else
       printf("complete getaddrinfo \n");
	#endif

	// loop through all the results and bind to the first we can
	for(p = servinfo; p != NULL; p = p->ai_next)
	{
		if ((sockfd = socket(p->ai_family, p->ai_socktype,
				p->ai_protocol)) == -1)
        {
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
                       )== -1)
        {//why should it be reusable?
			perror("setsockopt");
			exit(1);
		}
        #ifdef DEBUG
        else
            printf("complete setsockopt \n");
        #endif

		if (bind(sockfd, p->ai_addr, p->ai_addrlen) == -1)
		{
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

	if (p == NULL)
	{
		fprintf(stderr, "82. server: failed to bind. p == NULL\n");
		return 2;
	}
    #ifdef DEBUG
    else
        printf("complete p!=NULL \n");
    #endif

	freeaddrinfo(servinfo);
	// all done with this structure

	if (listen(sockfd, BACKLOG) == -1)
	{
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
                  ) == -1)
    {
		perror("sigaction");
		exit(1);
	}
    #ifdef DEBUG
    else
        printf("complete sigaction\n");
    #endif

	printf("server: waiting for connections...\n\n");

	while(1)
	{  // main accept() loop

		sin_size = sizeof their_addr;
		new_fd = accept(sockfd, (struct sockaddr *)&their_addr, &sin_size);
		if (new_fd == -1)
		{
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
		printf("SERVER: got connection from %s\n", s);

		if (!fork())
		{
		    #ifdef DEBUG
                printf("complete child fork()\n");
            #endif

			close(sockfd);
		    #ifdef DEBUG
                printf("complete child close(sockfd)\n");
            #endif


            rawtime = time ( NULL );//1970
            rawtime += 220898880;//1900
            rawtime = htonl (rawtime);
            p_msg = (char *) &rawtime;
            p_intmsg = (unsigned int *)p_msg;

			if ((send(new_fd,
                      p_msg,
                      sizeof(unsigned int),
                      0
                     )
                ) == -1
               )
				perror("send");
            #ifdef DEBUG
                printf("complete child send()\n");
                printf("complete child send(): %u\n",*((unsigned int *)p_msg));
                printf("complete child send(): %u\n",rawtime);
                printf("complete child send(): %u\n",*p_intmsg);
                printf("complete child send(): %s\n",ctime(p_msg));
            #endif

			close(new_fd);
			#ifdef DEBUG
                printf("complete child close(new_fd)\n");
            #endif

			exit(0);
		}

		close(new_fd);
		#ifdef DEBUG
            printf("complete parent close(new_fd)\n");
        #endif
	}//while(1)

	return 0;
}

