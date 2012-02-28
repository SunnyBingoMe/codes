#ifndef SFTPFUNCTIONDECLARATION_H_INCLUDED
#define SFTPFUNCTIONDECLARATION_H_INCLUDED

extern int openReadFile();
extern int closeReadFile();

extern int sendTheRequestFile();

extern int packSftpDataPacket();
extern int packTcpPacket();

extern int unpackTcpPacket();
extern int unpackSftpPacket();
extern int unpackSftpCommandPacket();
extern int unpackSftpSizeFilePacket();
extern int unpackSftpDataPacket();
extern int unpackSftpMessagePacket();


#endif // SFTPFUNCTIONDECLARATION_H_INCLUDED
