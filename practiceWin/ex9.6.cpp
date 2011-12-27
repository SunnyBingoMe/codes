#include<iostream>
using namespace std;
class Box
{
	public:
		Box(int h = 10, int w = 12, int len = 15):height(h),width(w),length(len){};
		int volume();
	private:
		int height;
		int width;
		int length;

};

int Box::volume()
{
	return height*width*length;

}

int main()
{
	Box a[3]=
		{
			Box(10,12,12),
			Box(1,1,1),
			Box(2,2,2)
		};
	int i = 0;
	for (i=0;i<=2;i++)
		cout<<"v of a["<<i<<"] is:"<<a[i].volume()<<endl;

}
