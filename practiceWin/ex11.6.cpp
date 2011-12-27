#include<iostream>
using namespace std;

class Student
{
	public:
		Student(int n, string nam)
		{
			num = n;
			name = nam;
		}
		void display()
		{
			cout<<"num:"<<num<<endl
			<<"name:"<<name<<endl;

		}
	protected:
		int num;
		string name;
};

class Student1 : public Student
{
	public:
		Student1(int n, string nam, int n1, string nam1, int a, string ad) : Student(n, nam), monitor(n1, nam1)
		{
			age = a;
			addr = ad;
		}
		void show()
		{
			cout<<"this student is "<<endl;
			display();
			cout<<"age:"<<age<<endl
			<<"addr:"<<addr<<endl;
		}
		void show_monitor()
		{
			cout<<endl<<"class monitor is "<<endl;
			monitor.display();
		}
	private:
		Student monitor;
		int age;
		string addr;
};

int main()
{
	Student1 stud1(10010, "wangli", 10001, "lisun", 19, "TsingHua uni");
	stud1.show();
	stud1.show_monitor();
	return 0;
}

