#include<iostream>
#include<iomanip>
#include<string>

using namespace std;

int main()
{
	int a;
	cout<<"please input a:";
	cin>>a;
	cout<<"dec:"<<dec<<a<<endl;
	cout<<"hex:"<<hex<<a<<endl;
	cout<<"oct:"<<setbase(8)<<a<<endl;
	
	string pt = "testStr";
	cout<<setw(10)<<pt<<endl;
	cout<<setfill('*')<<setw(10)<<pt<<endl;
	cout<<setw(10)<<pt<<endl;

	double pi = 22.0/7.0;
	cout<<setiosflags(ios::scientific)<<setprecision(8);
	cout<<"pi="<<pi<<endl;
	cout<<"pi="<<setprecision(4)<<pi<<endl;
	cout<<"pi="<<setprecision(ios::fixed)<<pi<<endl;
	return 0;
}