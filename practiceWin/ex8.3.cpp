#include <iostream>
using namespace std;

class myTime
{
	public:
		void set();
		void print();
	private:
		int hour;
		int minute;
		int second;
};

int main()
{
	myTime t1;
	t1.set();
	t1.print();
	myTime t2;
	t2.set();
	t2.print();

	return 0;
}

void myTime::set()
{
	cin>>hour>>minute>>second;
}

void myTime::print()
{
	cout<<hour<<":"<<minute<<":"<<second<<endl;
}

