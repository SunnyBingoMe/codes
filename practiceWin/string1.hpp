#ifndef STRING1_HPP_INCLUDED
#define STRING1_HPP_INCLUDED

#include <string.h>

class myString
{
	private:
		char * str;
	public:
		myString(char*s)
		{
			str = new char[strlen(s)+1];
			strcpy(str,s);
		}
		void print();
		~myString()
		{
			delete str;
		}
};

#endif // STRING1_HPP_INCLUDED
