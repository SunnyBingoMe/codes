#include<stdio.h>
#include<iostream>
using namespace std;

class Time
{
	public:
		Time(int,int,int);
		int hour;
		int minute;
		int sec;

		void getTime();

};

Time::Time(int h,int m, int s)
{
	hour = h;
	minute = m;
	sec = s;
}

void Time::getTime()
{
	printf("%i:%i:%i.\n",hour,minute,sec);

}

int main ()
{
	Time t1(12,23,3);
	int * const p1 = &t1.hour;
	cout<<*p1<<endl;
	t1.getTime();
	Time *p2 = &t1;
	p2->getTime();

	void (Time::*p3)();
	p3 = & Time::getTime;
	(t1.*p3)();

}
