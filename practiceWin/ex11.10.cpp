#include<iostream>
using namespace std;

class Student
{
	public:
		Student(int, string, float);
		void display();
	private:
		int num;
		string name;
		float score;
};

Student::Student(int n, string nam, float s)
{
	num = n;
	name = nam;
	score = s;
}

void Student::display()
{
	cout<<endl<<"num:"<<num<<endl
	<<"name:"<<name<<endl
	<<"score:"<<score<<endl;
}

class Graduate: public Student
{
	public:
		Graduate(int n, string nam, float s, float p):Student(n,nam,s),pay(p){};
		void display();
	private:
		float pay;
};

void Graduate::display()
{
	Student::display();
	cout<<"pay:"<<pay<<endl;
}

int main()
{
	Student stud1(1001,"li",87.5);
	Graduate grad1(2001,"wang",98.5,1234.5);
	Student * pt = &stud1;
	pt->display();
	pt = &grad1;
	pt->display();
	grad1.display();
}
