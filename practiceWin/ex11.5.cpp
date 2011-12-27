
#include<iostream>
using namespace std;

class Student
{
	public:
		Student(int n, string nam, char s)
		{
			num = n;
			name = nam;
			sex = s;
		}
		~Student(){}
	protected:
		int num;
		string name;
		char sex;
};

class Student1: public Student
{
	public:
		Student1(int n, string nam, char s, int a, string ad):Student(n, nam, s)
		{
			age = a;
			addr = ad;
		}
		void show()
		{
			cout<<"num:"<<num<<endl
			<<"nanme:"<<name<<endl
			<<"sex:"<<sex<<endl
			<<"age:"<<age<<endl
			<<"addr:"<<addr<<endl;
		}
		~Student1(){}
	private:
		int age;
		string addr;

};

int main()
{
	Student1 stud1(10010, "wangli", 'f', 19, "#115 TsingHua Uni.");
	stud1.show();
	return 0;
}
