#include<iostream>
using namespace std;

template <class numtype> class Compare
{
	public:
		Compare(numtype a, numtype b)
		{
			x = a;
			y = b;

		}
		numtype max()
		{
			return (x>y)?x:y;
		}
		numtype min()
		{
			return (x<y)?x:y;
		}
	private:
		numtype x,y;
};

int main()
{
	int p1 = 3, p2 = 7;
	Compare <int> cmp1(p1,p2);
	cout<<cmp1.max()<<" is the max."<<endl;
	cout<<cmp1.min()<<" is the min."<<endl;

	Compare <float> cmp2(234.325,3.5);
	cout<<cmp2.max()<<" is the max float."<<endl;

	return 0;
}
