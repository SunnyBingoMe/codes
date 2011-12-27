
#include<iostream>
using namespace std;

class Time
{
	public:
		Time(int,int,int);
		int hour;
		int minute;
		int sec;

};

Time::Time(int h, int m, int s)
{
	hour = h;
	minute = m;
	sec = s;
}

void fun(Time &t)
{
	t.hour = 18;
}

int main()
{
	Time t1(12,12,33);
	Time t2=t1, t3=t2;
	fun(t1);
	cout<<t1.hour<<endl;
	cout<<t3.hour<<endl;
	return 0;
}
