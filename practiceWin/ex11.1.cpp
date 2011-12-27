#include<iostream>
using namespace std;

class Student
{
	public:
		void get_value()
		{
			cin>>num>>name>>sex;
		}
		void display()
		{
			cout<<"num:"<<num<<endl
			<<"name:"<<name<<endl
			<<"sex:"<<sex<<endl;
		}
	private:
		int num;
		string name;
		char sex;
};

class Student1 : public Student
{
	public:
		void display_1()
		{
			display();
			cout<<"age:"<<age<<endl
			<<"address:"<<addr<<endl;
		}
	private:
		int age;
		string addr;
};

int main()
{

}
