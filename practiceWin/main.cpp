#include <iostream>
#include <string.h>

using namespace std;

class Girl;

class Boy
{
        char * name;
        int age;
    public :
        Boy(char * n, int a)
        {
            name = new char[strlen(n)+1];
            strcpy(name,n);
            age = a;
        }
        void disp(Girl &);
        ~Boy()
        {
            delete name;
        }
};

class Girl
{
        char * name, * dial;
        friend class Boy;
    public:
        Girl(char * n, char * d)
        {
                name = new char[strlen(n)+1];
                strcpy(name,n);
                dial = new char[strlen(d)+1];
                strcpy(dial,d);
        }
        ~Girl()
        {
            delete name;
            delete dial;
        }
};

void Boy::disp(Girl &x)
{
    cout<<"boy\'s name:"<<name
        <<",age:"<<age<<"\n";
    cout<<"girl's name:"<<x.name
        <<",tel:"<<x.dial<<endl;
}

int main()
{
    Boy b("bill", 25);
    Girl e("Eluza","2345");
    b.disp(e);

    return 0;
}
