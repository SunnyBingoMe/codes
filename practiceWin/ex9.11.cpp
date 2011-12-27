
#include<iostream>
using namespace std;

class Student
{
	public:
		Student(int n, int a, float s):num(n),age(a),score(s){}
		void total();
		float  average();
	private:
		int num;
		int age;
		float score;
		float static sum;
		int static count;
};

void Student::total()
{
	sum += score;
	count ++;
}
float Student::average()
{
	cout<<"number:"<<num<<endl;
	return(sum/count);
}
float Student::sum = 0;
int Student::count = 0;

int main()
{
	Student stud[3]=
	{
		Student(1001,13,32),
		Student(1002,19,78),
		Student(1005,20,98)
	};
	int n;
	cout<<"please number of students:";
	cin>>n;
	for(int i =0; i<n; i++)
	{
		stud[i].total();
	}
	cout<<"the average score of "<<n<<"students is "<<stud[1].average()<<endl;
	return 0;
}
