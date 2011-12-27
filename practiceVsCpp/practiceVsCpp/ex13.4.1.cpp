#include<iostream>

using namespace std;

int main()
{
	char * a = "BASIC";
	cout<<"ok a = "<<a<<endl;
	int i = 4;
	for (; i>=0; i--)
	{
		cout.put(*(a+i));
	}
//	cout.put('\n');
	return 0;
}