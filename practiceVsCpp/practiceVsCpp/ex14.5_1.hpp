#include<iostream>
#include<string>
#include<cmath>

using namespace std;

namespace ns1
{
	class Student
	{
	public:
		Student(int n, string nam, int a)
		{
			num = n;
			name = nam;
			age = a;
		}
		void getdata();
	private:
		int num;
		string name;
		int age;
	};
	void Student::getdata()
	{
		cout<<num<<""<<name<<""<<age<<endl;
	}
	double fun(double a, double b)
	{
		return sqrt(a+b);
	}
}