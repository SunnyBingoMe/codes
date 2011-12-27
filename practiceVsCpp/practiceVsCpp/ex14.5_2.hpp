#include<iostream>
#include<cmath>
#include<string>

using namespace std;

namespace ns2
{
	class Student
	{
	public:
		Student(int n, string nam, char s)
		{
			num = n;
			name = nam;
			sex = s;
		}
		void getdata();
	private:
		int num;
		string name;
		char sex;
	};
	void Student::getdata()
	{
		cout<<num<<""<<name<<""<<sex<<endl;
	}
	double fun(double a, double b)
	{
		return sqrt(a-b);
	}
}
