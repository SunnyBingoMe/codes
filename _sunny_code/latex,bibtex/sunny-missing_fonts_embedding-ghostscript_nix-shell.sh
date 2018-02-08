#http://stackoverflow.com/questions/4231656/how-do-i-embed-fonts-in-an-existing-pdf/4234150#4234150

gs \
  -sFONTPATH=/mnt/c/Windows/Fonts \
  -o _main-fonts-embeded.pdf \
  -sDEVICE=pdfwrite \
  -dPDFSETTINGS=/prepress \
   _main.pdf
   
