#include<iostream>
#include<string>
using namespace std;

class Student
{
public:
	Student(int n, string nam)
	{
		cout<<"ok constructor_"<<n<<endl;
		num = n;
		name = nam;
	}
	~Student()
	{
		cout<<"ok destructor_"<<num<<endl;
	}
	void get_data();
private:
	int num;
	string name;
};

void Student::get_data()
{
	cout<<"getdata start"<<endl;
	if(num == 0)
	{
		throw num;
	}
	else
	{
		cout<<num<<","<<name<<endl;
		cout<<"get_data() end"<<endl;
	}
}
void fun()
{
	Student stud1(1101,"tan");
	stud1.get_data();
	Student stud2(0,"li");
	stud2.get_data();
}

int main()
{
	cout<<"main begin"<<endl;
	cout<<"call fun()"<<endl;
	try
	{
		fun();
	}
	catch(int n)
	{
		cout<<"ǰ��������� ���� �� obj �뿪��������ʱ���� obj"<<endl;
		cout<<"Num = "<<n<<endl;
	}
	cout<<"main end"<<endl;
	return 0;
}

