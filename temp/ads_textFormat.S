COUNT		EQU		0x30003100	; 定义一个变量，地址为0x30003100	
			
			AREA	Example,CODE,READONLY	; 声明代码段Example 
			ENTRY				; 标识程序入口
			CODE32				; 声明32位ARM指令
START		LDR		R1,=COUNT	; R1 <= COUNT
			MOV		R0,#0		; R0 <= 0 
			STR		R0,[R1]		; [R1] <= R0，即设置COUNT为0
						
LOOP   		LDR		R1,=COUNT	
			LDR		R0,[R1]		; R0 <= [R1] 
			ADD		R0,R0,#1	; R0 <= R0 + 1
			STR		R0,[R1]		; [R1] <= R0，即保存COUNT
			
			B		LOOP
			
			END	
