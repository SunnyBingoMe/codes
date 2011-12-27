#include<iostream>
using namespace std;
class myBox
{
	public:
		myBox(int h = 10, int w = 10, int l =10 );
		int volume()
		{
			return height*width*length;
		}
		int heightGet()
		{
			return height;
		}
	private:
		int height;
		int width;
		int length;
};

myBox::myBox(int h, int w, int l)
{
	height = h;
	width = w;
	length = l;
}

int main()
{
	myBox b1;
	cout<<"v1 is "<<b1.volume()<<endl;
	myBox b2(15);
	cout<<"v2 is "<<b2.volume()<<", height is "<<b2.heightGet()<<endl;
	myBox b3(15,15,15);
	cout<<"v3 is "<<b3.volume()<<endl;
	return 0;
}
