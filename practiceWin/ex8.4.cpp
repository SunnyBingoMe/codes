#include <iostream>
using namespace std;

class arrayMax
{
	public:
		void set();
		void calculateMax();
		void print();
	private:
		int array[10];
		int max;
};

void arrayMax::set()
{
	int i;
	for (i = 0; i < 10; i++)
	{
		cin>>array[i];
	}
}
void arrayMax::calculateMax()
{
	int i;
	max = array[0];
	for(i = 1; i<10; i++)
	{
		if(array[i] > max)
		{
			max = array[i];
		}
	}
}
void arrayMax::print()
{
	cout<<"max:"<<max;
}

int main()
{
	arrayMax tArrayMax;
	tArrayMax.set();
	tArrayMax.calculateMax();
	tArrayMax.print();

	return 0;
}

