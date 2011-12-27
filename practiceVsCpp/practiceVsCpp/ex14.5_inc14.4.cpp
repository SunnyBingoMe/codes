#include<iostream>
#include"ex14.5_1.hpp"
#include"ex14.5_2.hpp"

using namespace std;

int main()
{
	ns1::Student stud1(101,"wang",18);
	stud1.getdata();
	cout<<ns1::fun(5,3)<<endl;
	ns2::Student stud2(102, "li", 'f');
	stud2.getdata();
	cout<<ns2::fun(5,3)<<endl;
	return 0;
}
