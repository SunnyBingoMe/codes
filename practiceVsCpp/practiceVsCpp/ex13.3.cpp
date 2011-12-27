#include<iostream>

using namespace std;

int main()
{
	int a = 21;
	
	cout.setf(ios::showbase);
	cout<<"dec:"<<a<<endl;
	cout.unsetf(ios::dec);
	
	cout.setf(ios::hex);
	cout<<"hex:"<<a<<endl;
	cout.unsetf(ios::hex);

	cout.setf(ios::oct);
	cout<<"oct:"<<a<<endl;
	cout.unsetf(ios::oct);


	char * pt = "tStr";
	
	cout.width(10);
	cout<<pt<<endl;
	
	cout.width(10);
	cout.fill('*');
	cout<<pt<<endl;
	
	double pi = 22.0/7.0;
	cout.setf(ios::scientific);
	cout<<"scientific,pi="<<pi<<endl;
	cout.width(18);
	cout<<"width(18),pi="<<pi<<endl;
	cout.setf(ios::showpos);//show "+"
	cout.setf(ios::internal);// the "+" is on the left side
	cout.precision(6);
	cout<<pi<<endl;
	return 0;
}