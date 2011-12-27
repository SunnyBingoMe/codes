#include <iostream>
using namespace std;

int main()
{
	class cube
	{
		private:
			float a,b,c;
		public:
			cube()
			{
				cout<<"please input the a,b,c:"<<endl;
				cin>>a>>b>>c;
			}
			int volume()
			{
				cout<<"the volume is:"<<a*b*c<<endl;
				return 0;
			}
	}
	class cube tCube;
	tCube.volume();

	return 0;
}
