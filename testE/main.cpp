#include <string.h>
#include <iostream>
using namespace std;
class Person
{
    private :
        char name[10];
        int age;
        char sex;
    public :
        void print();
		Person(char * n, int a, char s);
};

void Person::print()
{
    cout<<"name:"<<name;
    cout<<",age:"<<age;
    cout<<",sex:"<<sex<<endl;
}
 
Person::Person(char * n, int a, char s)
{
    strcpy(name,n);
    age = a;
    sex = s;
}

void main()
{
	Person zh("zhang");
	zh.print();

}
