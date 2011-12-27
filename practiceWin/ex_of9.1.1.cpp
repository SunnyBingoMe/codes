#include <iostream>
using namespace std;

class myTime
{
	public:
		myTime()
		{
			hour=0;
			minute=0;
			second=0;
		}
		void set_time();
		void show_time();
	//private:
		int hour;
		int minute;
		int second;
};

void myTime::set_time()
{
	cin>>hour>>minute>>second;
}
void myTime::show_time()
{
	cout<<"time:"<<hour<<":"<<minute<<":"<<second<<endl;
}

int main()
{
	myTime t1;
	t1.set_time();
	t1.show_time();

	myTime t2;//=(1,1,1);
	t2.show_time();

	return 0;
}
