#include<iostream>
//#include<string>
using namespace std;

class Student
{
	public:
		void display();
	protected:
		int num;
		string name;
		char sex;
};

void Student::display()
{
	cout<<"num:"<<num<<endl
	<<"name:"<<name<<endl
	<<"sex:"<<sex<<endl;
}

class Student1: protected Student
{
	public:
		void display1();
	private:
		int age;
		string addr;
};

void Student1::display1()
{
	cout<<"num:"<<num<<endl
	<<"name:"<<name<<endl
	<<"sex:"<<sex<<endl
	<<"age:"<<age<<endl;

}

int main()
{
	Student1 stud1;
	stud1.display1();

	return 0;
}
