#include<iostream>
using namespace std;

int main()
{
	void f1();
	try
	{
		f1();
	}
	catch(double)
	{
		cout<<"ok 0!"<<endl;
	}
	cout<<"end 0"<<endl;
	return 0;
}

void f1()
{
	void f2();
	try
	{
		f2();
	}
	catch(char)
	{
		cout<<"ok 1!"<<endl;
	}
	cout<<"end 1"<<endl;
}

void f2()
{
	void f3();
	try
	{
		f3();
	}
	catch(int)
	{
		cout<<"ok 2 !"<<endl;
	}
	cout<<"end 2"<<endl;
}

void f3()
{
	double a = 0;
	try
	{
		throw a;
	}
	catch(double)
	{
		cout<<"ok 3"<<endl;
		throw a;
	}
	cout<<"end 3"<<endl;
}
