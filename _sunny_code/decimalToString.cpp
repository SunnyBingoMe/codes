#include <iostream>
#include <string>
#include <sstream>
using namespace std;

int main() {
	int num;
    stringstream buf;
    string s;

	cout << "Enter a number: ";
    cin >> num;

    buf << num;
    buf >> s;
    buf.clear();

	cout << "In string: " << s << endl;
	return 0;
}
