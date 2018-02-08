#! /usr/bin/python3
import re
import os

with open('Library.bib', 'rt', encoding='utf-8') as file_read:
    with open('LibraryT.bib', 'wt', encoding='utf-8') as file_write:
        for one_line in file_read:
            one_line = re.sub(r'note\s*=\s*{', r'XXXnote = {', one_line)
            one_line = re.sub(r'isbn\s*=\s*{', r'XXXisbn = {', one_line)
            one_line = re.sub(r'issn\s*=\s*{', r'XXXissn = {', one_line)
            one_line = re.sub(r'doi\s*=\s*{', r'XXXdoi = {', one_line)
            one_line = re.sub(r'Ieee', r'{IEEE}', one_line)
            one_line = re.sub(r'Iet ', r'{IET} ', one_line)
            one_line = re.sub(r'Acm ', r'{ACM} ', one_line)
            #one_line = re.sub(r'url\s*=\s*{', r'XXXurl= {', one_line)
            file_write.write(one_line)
os.remove("Library.bib")
os.rename("LibraryT.bib", "Library.bib")
