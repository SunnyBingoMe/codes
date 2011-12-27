#include<iostream>
using namespace std;
//#define NON_VIRTUAL

class Point
{
public:
	//virtual void test() = 0; // error : 不允许实例化抽象类
	Point(float x = 0, float y = 0);
	void setPoint(float, float);
	float getX() const
	{
		return x;
	}
	float getY() const
	{
		return y;
	}
	friend ostream & operator << (ostream &, const Point &);
protected:
	float x,y;
public:
#ifdef NON_VIRTUAL
	~Point()
	{
		cout<<"executing Point destructor\n";
	}
#else
	virtual ~Point()
	{
		cout<<"executing Point destructor\n";
	}
#endif
};

Point::Point(float a, float b)
{
	x = a;
	y = b;
}
void Point::setPoint(float a, float b)
{
	x = a;
	y = b;
}
ostream & operator << (ostream & output, const Point & p)
{
	output<<"["<<p.x<<","<<p.y<<"]"<<endl;
	return output;
}

class Circle : public Point
{
public:
	Circle(float x = 0, float y = 0, float r = 0);
	void setRadius(float);
	float getRadius() const;
	float area() const;
	friend ostream & operator << (ostream &, const Circle &);
private:
	float radius;
public:
	~Circle()
	{
		cout<<"executing Circle destructor"<<endl;
	}
};

Circle::Circle(float a, float b, float r): Point(a,b)
{
	radius = r;
}
void Circle::setRadius(float r)
{
	radius = r;
}
float Circle::getRadius() const
{
	return radius;
}
float Circle::area() const
{
	return 3.14*radius*radius;
}
ostream & operator << (ostream & output, const Circle & c)
{
	output<<"center=["<<c.getX()<<","<<c.getY()<<"].\nr="<<c.getRadius()<<"\narea="<<c.area()<<endl;
	return output;
}

class Cylinder : public Circle
{
public:
	Cylinder (float x = 0, float y = 0, float r = 0, float h = 0);
	void setHeight(float);
	float getHeight() const;
	float area() const;
	float volume() const;
	friend ostream & operator << (ostream &, const Cylinder &);
protected:
	float height;
public:
	~Cylinder()
	{
		cout<<"executing Cylinder destructor"<<endl;
	}
};

Cylinder::Cylinder(float a, float b, float r, float h):Circle(a,b,r)
{
	height = h;
}
void Cylinder::setHeight(float h)
{
	height = h;
}
float Cylinder::getHeight() const
{
	return height;
}
float Cylinder::area() const
{
	return 2*Circle::area()+2*3.14*Circle::getRadius()*height;
}
float Cylinder::volume() const
{
	return Circle::area()*height;
}
ostream & operator << (ostream & output, const Cylinder & cy)
{
	output<<"center=["<<cy.x<<","<<cy.y<<"]\nr="<<cy.Circle::getRadius()<<"\nh="<<cy.height<<"\narea="<<cy.area()<<"\nvolume="<<cy.volume()<<endl;
	return output;
}

// test classCylinder
int main()
{
	Cylinder cy1(3.5, 6.4, 5.2, 10);
	cout<<cy1;
	cy1.setHeight(15);
	cy1.setRadius(7.5);
	cy1.setPoint(5,5);
	cout<<"\nnew cy1: \n"<<cy1;
	Point & pRef = cy1;
	Point * ppRef = & cy1;
	cout<<"\npRef as a point:\n"<<pRef;
	cout<<"\nppRef as a point:\n"<<*ppRef;
/*	pRef = (Circle &) pRef; //强制转换无效
	cout<<"\npRef as a circle:\n"<<pRef;
*/	Circle & cRef = cy1;
	cout<<"\ncRef as a circle:\n"<<cRef;
	cout<<"test chinese: 测试汉字显示。test end\n";
#ifdef NON_VIRTUAL
	cout<<"test non-virtual destructor:"<<endl;
	Point * testDestructor = new Circle;
	delete testDestructor;
	cout<<endl;
#else
	cout<<"test virtual destructor:"<<endl;
	Point * testDestructor = new Circle;
	delete testDestructor;
	cout<<endl;
#endif
	return 0;
}


/* test classCircle
int main()
{
	Circle c(3.5,6.4,5.2);
	cout<<c;
	c.setRadius(7.5);
	c.setPoint(5,5);
	cout<<"\nnew:\n"<<c;
	Point & pRef = c;
	cout<<"pRef:"<<pRef;
	return 0;
}
*/
