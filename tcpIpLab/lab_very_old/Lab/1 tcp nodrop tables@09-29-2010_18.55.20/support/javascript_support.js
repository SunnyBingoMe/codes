/*---------------------------------------------*/
/* Javasript Support Functions				  -*/
/*---------------------------------------------*/

/** Javascript Table Functions ***/
/**------------------------------*/

var use_css=false;
var use_layers=false;	
var use_dom=false;
if (document.all)	 { use_css	  = true; }
if (document.layers) { use_layers = true; }
if (document.getElementById) { use_dom=true; }

var sort_object;
var sort_column;
var reverse=0;

//Invoke Table Editor
function TableEditor (anchor, tb)
{
	var result = "";

	//write entire HTML document
	result += "<HTML><HEAD><TITLE>Table Properties</TITLE>";
	result += "<SCRIPT TYPE='text/javascript' LANGUAGE='JavaScript' SRC='../support/javascript_support.js'> </SCRIPT>";
	result += "</HEAD>\n";
	result += "<BODY onLoad ='DisableThresholdField()' BGCOLOR='#FFFFFF' LINK='#0000FF' VLINK='#FF0000' ALINK='#008787' MARGINWIDTH=0 MARGINHEIGHT=0 TOPMARGIN=0 RIGHTMARGIN=0 LEFTMARGIN=0>\n";
	result += "<form name=SortForm><TABLE border=2 cellspacing=1 cellpadding=5 width=100%>";
	result += "<TR bgcolor=#CCCCFF>";
	result += "<TD border=3 align=center><B>Shown Columns</B></TD>";
	result += "<TD align=left></TD>";
	result += "<TD align=center><B>Hidden Columns</B></TD></TR>"; 
	result += "<TR bgcolor=#CCCCCC><TD width='150'><SELECT NAME='list1' MULTIPLE SIZE=10 onDblClick='moveSelectedOptions(document.forms[0].list1,document.forms[0].list2,true)'>\n";
	for (var k=0; k < tb.Columns.length; k++) 
		{
		if (tb.Columns[k].display == 1)
			{
			result += "<OPTION VALUE="+'"'+tb.Columns[k].name+'"'+">"+tb.Columns[k].name+"</OPTION>\n";
			}
		}
	
	result += "</SELECT></TD>\n";

	// Populate rows
	result += "<TD VALIGN=MIDDLE ALIGN=CENTER width=100>\n";
	result += "<INPUT TYPE='button' NAME='right' VALUE='&gt;&gt;' ONCLICK='moveSelectedOptions(document.forms[0].list1,document.forms[0].list2,false)'><BR><BR>\n";
	result += "<INPUT TYPE='button' NAME='right' VALUE='All &gt;&gt;' ONCLICK='moveAllOptions(document.forms[0].list1,document.forms[0].list2,false)'><BR><BR>\n";
	result += "<INPUT TYPE='button' NAME='left' VALUE='&lt;&lt;'ONCLICK='moveSelectedOptions(document.forms[0].list2,document.forms[0].list1,false)'><BR><BR>\n";
	result += "<INPUT TYPE='button' NAME='left' VALUE='All &lt;&lt;' ONCLICK='moveAllOptions(document.forms[0].list2,document.forms[0].list1,false)'>\n";
	result +="</TD>\n";

	result += "<TD width='150'><SELECT NAME='list2' MULTIPLE SIZE=10 onDblClick='moveSelectedOptions(document.forms[0].list2,document.forms[0].list1,false)'>\n";
	for (var k=0; k< tb.Columns.length; k++) 
		{
		if (tb.Columns[k].display == 0)
			{
			result += "<OPTION VALUE="+'"'+tb.Columns[k].name+'"'+">"+tb.Columns[k].name+"</OPTION>\n";
			}
		}
	
	result += "</SELECT></TD></TR>\n";
	result += "<TR></TR> </TABLE> <br>"
	result += "<TABLE border=2 cellspacing=1 cellpadding=5 width=100%><TR bgcolor=#CCCCFF>"
	result +="<TH colspan=3><b>Data Visualization Settings<b></TH>"
	result += "</TR>"

	result += "<TR bgcolor=#CCCCFF align=center><TD>Column Name </TD><TD>Display Type</TD><TD>Threshold</TD></TR>"
	result += "<tr bgcolor=#CCCCCC><td border=0 align=left>"
	result += "<select name=v_col_name height='20'>\n";
	
	for (var k=0; k< tb.Columns.length; k++) 
		{
		if ((tb.Columns[k].display == 1) && 
			(tb.Columns[k].type == "numeric") &&
			(tb.Columns[k].name != "Line#"))
			{
			result += "<OPTION VALUE="+'"'+tb.Columns[k].name+'"'+">"+tb.Columns[k].name+"</OPTION>\n";
			}
		}
	result += "	</select></td>\n";

	result += "<td border=0 align=left><select name=type height='20' onchange='DisableThresholdField()'>\n";
	if (g_sort_type == 0)
		{
		result += "		<OPTION VALUE="+'"'+0+'"'+">Text </OPTION>\n";
		result += "		<OPTION VALUE="+'"'+1+'"'+">Graphical</OPTION>\n";
		}
	else
		{
		result += "		<OPTION VALUE="+'"'+1+'"'+">Graphical</OPTION>\n";
		result += "		<OPTION VALUE="+'"'+0+'"'+">Text </OPTION>\n";
		}
	
	result += "	</select></td>\n";
	result += "<td border=0 align=left><input name=thresh value="+g_threshold+" type=text height=20 size=10></td>\n";
	result += "</tr> </TABLE>"


	result += "<TABLE border=0 cellspacing=1 cellpadding=5 width=100%><tr>";
	result += "<td border=0></td>"
	result += "<td border=0></td>"
	result += "<td border=0  height=25 valign=middle align=right>";
	result += "<input type='button' value='Update' onClick='window.opener.document.clear();window.opener.UpdateTable (document.forms[0].list1,document.forms[0].list2, document.SortForm.v_col_name.value, document.SortForm.type.value, document.SortForm.thresh.value);window.close();'>";
	result += "&nbsp;";
	result += "<input type='button' value='Cancel' onClick='window.close();'>";
	result += "&nbsp;";
	result += "</td></tr>";
	result += "</TABLE></FORM>\n";
	result += "</BODY></HTML>\n";		

	// Create an object for a WINDOW popup
	var win = new PopupWindow(); 
	win.setSize(490,370);
	win.populate(result);
	win.showPopup("anchor");
}


// Constructor for SortTable object
function SortTable(name) {
	// Properties
	this.name = name;
	this.sortcolumn="";
	this.dosort=true;
	this.tablecontainsforms=false;
	// Methods
	this.AddLine = AddLine;
	this.AddElement = AddElement;
	this.AddPreformattedElement = AddPreformattedElement;
	this.AddColumn = AddColumn;
	this.WriteRows = WriteRows;
	this.WriteHeader = WriteHeader;
	this.RemoveColumns = RemoveColumns;
	this.RestoreColumns = RestoreColumns;
	this.SortRows = SortRows;
	this.AddLineProperties = AddLineProperties;
	this.AddLineSortData = AddLineSortData;
	this.ResetTable = ResetTable;
	this.ComputeColumnDataType = ComputeColumnDataType;
	this.ResetColumn = ResetColumn;
	// Structure
	this.Columns = new Array();
	this.Lines = new Array();
	this.LineProperties = new Array();
	this.doc = document;
	}


// only supports IE and newer Netscapes
function DisplaySlowLoadWarning (element_name, warning)
{
var result = "";
result += "<B>"+warning+"</B>";

if (use_dom) 
		document.getElementById ('PROGRESSBAR').innerHTML = result;
else if (use_css) 
		document.getElementById ('PROGRESSBAR').innerHTML = result;
}

// only supports IE and newer Netscapes
function HideSlowLoadWarning (element_name)
{
if (use_dom) 
		document.getElementById(element_name).innerHTML = "";
else if (use_css) 
		document.getElementById(element_name).innerHTML = "";
}

// Loop through every column and determine
// whether all info in it is numeric or 
// text
function ComputeColumnDataType ()
	{
	for (var i=0; i<this.Columns.length; i++) 
		{
		
		this.Columns[i].type = "numeric";

		for (var j=0; j<this.Lines.length; j++) 
			{
			if (!IsNumeric (this.Lines[j][i].data))
				{
				this.Columns[i].type = "text";
				break;
				}
			else
				{
				// make sure we don't have any comma separators for appropriate sorting				
				this.Lines[j][i].data = removestr (this.Lines[j][i].data, ",");
				}
			}		
		}
	}


function ResetColumn (column_name, sort_type, threshold)
	{

	var colm_index = -1;

	//Determine the index of the column
	for (var i=0; i<this.Columns.length; i++)
		{
		if (this.Columns[i].name == column_name)
			{
			colm_index = i;
			break;
			}
		}		

	if (colm_index < 0)
		return;

	var link_name = "";
	
	// indicate that the sort type/or threshold for the column has changed
	if ((this.Columns[colm_index].sort_type != sort_type) ||
		(this.Columns[colm_index].threshold != threshold))
		{				
		this.Columns[colm_index].sort_type = sort_type;
		this.Columns[colm_index].threshold = threshold;
		this.Columns[colm_index].change = 1;
		}
	else
		{
		// nothing changed for this column, exit function
		return;
		}

	if (sort_type == 1)
		{
		// Determine max value for this column
		max_value = 0;
		for (var i=0; i<this.Lines.length; i++) 
			{
			if (!IsNumeric (this.Lines[i][colm_index].data))
				continue;

			if (parseFloat(this.Lines[i][colm_index].data) >= max_value)
						max_value = parseFloat(this.Lines[i][colm_index].data);
			}

		// Now update text for this column to show bars
		for (var i=0; i<this.Lines.length; i++) 
			{
			this.Lines[i][colm_index].text = CreateSingleBar (this.Lines[i][colm_index].data, threshold, 150, max_value, this.Lines[i][colm_index].uf_data);
			}
		
		// Now update the column header
		link_name += "<b><a href="+'"'+"javascript:SortRows("+this.name+","+colm_index+")"+'"'+">"+this.Columns[colm_index].name+ " (threshold = "+threshold+")</a></b>";	
		this.Columns[colm_index].hname = link_name;		 		
		}
	else
		{
		// Now update text for this column to just show numbers
		for (var i=0; i<this.Lines.length; i++) 
			{
			this.Lines[i][colm_index].text = this.Lines[i][colm_index].uf_data;
			}

		link_name += "<b><a href="+'"'+"javascript:SortRows("+this.name+","+colm_index+")"+'"'+">"+this.Columns[colm_index].name+ "</a></b>";	
		this.Columns[colm_index].hname = link_name;	
		}
	}


// This function is used to eather hide or display 
// text inside each column, depending on its status
function ResetTable ()
	{
	var table = this;
	for (var i=0; i<table.Lines.length; i++) {
		for (var j=0; j<table.Columns.length; j++) 
			{			
			var cell_name = "d"+table.name+"-"+i+"-"+j;
			var h_name = "d"+table.name+"-"+j;
			
			// skipped unchanged columns
			if (table.Columns[j].change == 0)
				continue;
			 
			if (use_dom) 
				{
				if (table.Columns[j].display == 0)
					{	
					document.getElementById(cell_name).innerHTML = "";
					document.getElementById(h_name).innerHTML = "";
					}
				else
					{
					document.getElementById(cell_name).innerHTML = table.Lines[i][j].text;
					document.getElementById(h_name).innerHTML = table.Columns[j].hname;
					}
				}
			else if (use_css) {
				if(table.Columns[j].type == "form") {
					if (table.Columns[j].display == 0)
						{	
						document.all[cell_name].innerHTML = "";
						document.all[h_name].innerHTML = "";
						}
					else
						{
						document.getElementById(cell_name).innerHTML = table.Lines[i][j].text;
						document.getElementById(h_name).innerHTML = table.Columns[j].hname;
						}		
					}
				else {
					if (table.Columns[j].display == 0)
						{	
						document.all[cell_name].innerHTML = "";
						document.all[h_name].innerHTML = "";
						}
					else
						{
						document.all[cell_name].innerHTML = table.Lines[i][j].text;
						document.all[h_name].innerHTML = table.Columns[j].hname;
						}			
					}		
				}
			else if (use_layers) {
			   var cell_namex= "d"+table.name+"-"+i+"-"+j+"x";
			   var h_namex= "d"+table.name+"-"+j+"-"+"x";
						   if (table.Columns[j].align != '') {
				document.layers[cell_name].document.layers[cell_namex].document.write("<SPAN CLASS=\""+table.Columns[j].align+"\">");
				}
				if (table.Columns[j].display == 0)
						{
					document.layers[cell_name].document.layers[cell_namex].document.write("");
						document.layers[cell_name].document.layers[h_namex].document.write("");
					}
				else
					{
					document.layers[cell_name].document.layers[cell_namex].document.write(table.Lines[i][j].text);
						document.layers[cell_name].document.layers[h_namex].document.write(table.Columns[j].hname);
									}

				if (table.Columns[j].align != '') {
					document.layers[cell_name].document.layers[cell_namex].document.write("</SPAN>");
					}
				document.layers[cell_name].document.layers[cell_namex].document.close();
			}
		 }
	}
	//window.location.reload();
}

// Add a line to the grid
function AddLine(odd_even) {
	var index = this.Lines.length;
	this.Lines[index] = new Array();
	if (odd_even == "even")
		{
		this.Lines[index].tag_open = "<tr class=row_even onClick='HighLightTR(0);'>";
		this.Lines[index].tag_close = "</tr>";
		}
	else
		{
		this.Lines[index].tag_open = "<tr class=row_odd onClick='HighLightTR(1);' >";
		this.Lines[index].tag_close = "</tr>"
		}
	}

//Add an element to the line
function AddElement(element, selement, popup, popup_path)
	{
	var path = "";
	var index = this.Lines.length-1;
	var i = this.Lines[index].length;
	this.Lines[index][i] = new Object();
	
	if (popup =="yes")
		{
		path = "<a href="+'"'+"javascript:PopUp("+"'"+popup_path+"'"+",'1','1','1','800','200'"+')'+'"'+'>'+element+"</a>";
		this.Lines[index][i].text = path;
		}
	else
		{
		this.Lines[index][i].text = element;
		}

	this.Lines[index][i].data = selement;	
	this.Lines[index][i].uf_data = selement;
	}

//Add a message element to the line (the Message cell
//for a log table message).  The cell is preformatted
// text.
function AddPreformattedElement(element_input)
	{
	var index = this.Lines.length-1;
	var i = this.Lines[index].length;
	var element = '<pre>'+element_input+'</pre>';

	this.Lines[index][i] = new Object();
	
	this.Lines[index][i].text = element;

	this.Lines[index][i].data = element;
	this.Lines[index][i].uf_data = element;
	}


// Define properties for the <TR> of the last line added
function AddLineProperties(prop) {
	var index = this.Lines.length-1;
	this.LineProperties[index] = prop;
	}

// Define sorting data for the last line added
function AddLineSortData() {
	var index = this.Lines.length-1;
	for (var i=0; i<arguments.length; i++) {
		if (arguments[i] != '') {
			this.Lines[index][i].data = arguments[i];
			}
		}
	}

// Add a column definition to the table
// Arguments:
//	 name = name of the column
//	 td   = any arguments to go into the <TD> tag for this column (ex: BGCOLOR="red")
//	 align= Alignment of data in cells
//	 type = type of data in this column (numeric, money, etc) - default alphanumeric
function AddColumn(name,td,align,type) {
	var index = this.Columns.length;
	this.Columns[index] = new Object;
	this.Columns[index].name = name;
	this.Columns[index].td	 = td;
	this.Columns[index].align=align;
	this.Columns[index].type = type;
	this.Columns[index].display = 1;
	this.Columns[index].change = 1;
	this.Columns[index].sort_type = 0;
	this.Columns[index].threshold = 0;
	if (type == "form") {
		 this.tablecontainsforms=true; 
		 if (use_layers) { 
			this.dosort=false;
			}
		}
	}


// Hides each column in the list
function RemoveColumns (list)
{
for (var k = 0; k < list.length; k++)
	{
	for (var i = 0; i < this.Columns.length; i++)
		{
		if (this.Columns[i].name == list.options[k].text)
			{
			if (this.Columns[i].display != 0)
				{
				this.Columns[i].display = 0;
				this.Columns[i].change = 1;
				}
			else
				{
				this.Columns[i].change = 0;
				}
			}
		}		
	}
}		


// Restores visibility status of each column in the list
function RestoreColumns(list)
{
for (var k = 0; k < list.length; k++)
	{
	for (var i = 0; i < this.Columns.length; i++)
		{
		if (this.Columns[i].name == list.options[k].text)
			{
			if (this.Columns[i].display != 1)
				{
				this.Columns[i].display = 1;
				this.Columns[i].change = 1;
				}
			else
				{
				this.Columns[i].change = 0;
				}
			}
		}		
	}
}		

// write header row
function WriteHeader(sort_type, threshold)
{

// set edit button
//DisableThresholdField ();

var result = "";
result += "<table><tr class=row_head>";

for (i = 0; i < this.Columns.length; i++)
	{
	
	if (this.Columns[i].display == 0)
		continue;	

	var div_name = "d"+this.name+"-"+i;
	var open_div = "<DIV ID="+div_name+">";
	var close_div= "</DIV>";
	result +="<TD class='row_head'>"+open_div;
	var link_name = "";
	
	// first column is always line #
	if (i == 0)
		link_name += "<b>"+this.Columns[i].name+"</b>";
	else
		link_name += "<b><a href="+'"'+"javascript:SortRows("+this.name+","+i+")"+'"'+">"+this.Columns[i].name+"</a></b>";
	
	result += link_name;
	result += close_div+ "</TD>\n";
	this.Columns[i].hname = link_name;
	}

result += "</tr>";
this.doc.write (result);
}


// Print out the original set of rows in the grid
function WriteRows() 
{
	var result = "";
	var open_div = "";
	var close_div = "";
	var day = new Date();
	var id = day.getTime();
	var popup_window_set = 0;

	for (var i=0; i<this.Lines.length; i++) {
	
	// flush periodically
	if (result.length > 300)
		{	
		this.doc.write (result); 
		result = "";
		}

	result += this.Lines[i].tag_open;
	for (var j=0;j<this.Columns.length;j++)
		{		
		if (this.Columns[j].display == 0)
			continue;
		
		var div_name = "d"+this.name+"-"+i+"-"+j;			
		if (use_css || use_dom) {
			if (this.Columns[j].align != '') {
				var align = " ALIGN="+this.Columns[j].align;
				}
			else {
				var align = "";
				}
			open_div = "<DIV ID=\""+div_name+"\" "+align+">";
			close_div= "</DIV>";
			}
		else if (use_layers) {
			// If the table contains form elements, don't use <LAYER> tags or the
			// form will be forced closed.
			if (!this.dosort) {
				if (this.Columns[j].align != '') {
					open_div="<SPAN CLASS=\""+this.Columns[j].align+"\">";
					}
				}
			else {
				open_div = "<ILAYER NAME=\""+div_name+"\" WIDTH=100%>";
				open_div+= "<LAYER NAME=\""+div_name+"x\" WIDTH=100%>";
				if (this.Columns[j].align != '') {
					open_div+= "<SPAN CLASS=\""+this.Columns[j].align+"\">";
					}
				}
			if (this.Columns[j].align != '') {
				close_div = "</SPAN>";
				}
			if (this.dosort) {
				close_div += "</LAYER></ILAYER>";
				}
			}
	
		// first column is always line #
		if (j == 0)
		  result += "<TD "+this.Columns[j].td+">"+open_div+i+close_div+"</TD>\n";
		else	
		  result += "<TD "+this.Columns[j].td+">"+open_div+this.Lines[i][j].text+close_div+"</TD>\n";
		}
	result+= this.Lines[i].tag_close;
	}
this.doc.write (result); 

// Also set types on each column for sorting
this.ComputeColumnDataType ();
}


// Sort the table and re-write the results to the existing table
function SortRows(table,column) {
	sort_object = table;
	if (!sort_object.dosort) { return; }
	if (sort_column == column) { reverse=1-reverse; }
	else { reverse=1; }
	sort_column = column;

	// Save all form column contents into a temporary object
	// This is a nasty hack to keep the current values of form elements intact
	if (table.tablecontainsforms) {
		var iname="1";
		var tempcolumns = new Object();
		for (var i=0; i<table.Lines.length; i++) {
			for (var j=0; j<table.Columns.length; j++) {
			
				if (table.Columns[j].display == 0)
					continue;
			
				if(table.Columns[j].type == "form") {
					var cell_name = "d"+table.name+"-"+i+"-"+j;
					if (use_css) {
						tempcolumns[iname] = document.all[cell_name].innerHTML;
					}
					else {
						tempcolumns[iname] = document.getElementById(cell_name).innerHTML;
					}
					table.Lines[i][j].text = iname;
					iname++;
					}
				}
			}
		}
	
	if (table.Columns[column].type == "numeric") {
		// Sort by Float
		table.Lines.sort(	function by_name(a,b) 
		 {
		 if (parseFloat(a[column].data) < parseFloat(b[column].data) ) { return -1; }
		 if (parseFloat(a[column].data) > parseFloat(b[column].data) ) { return 1; }
		 return 0;});
		}
	else {
		// Sort by alphanumeric
		table.Lines.sort(	function by_name(a,b) {
		 if (a[column].data+"" < b[column].data+"") { return -1; }
		 if (a[column].data+"" > b[column].data+"") { return 1; }
		 return 0;});
		}

	if (reverse) { table.Lines.reverse(); }
	
	// redisplay rows
	for (var i=0; i<table.Lines.length; i++) 
		{
		var line_num = "";
		line_num += i;

		for (var j=0; j<table.Columns.length; j++) 
		{
		if (table.Columns[j].display == 0)
			continue;		

		var cell_name = "d"+table.name+"-"+i+"-"+j;
		var h_name = "d"+table.name+"-"+j;

		// Indicate the direction of the sorted column
		//-----------------------------------
		var img_src = "";
			
		if (j == column)
			{
			if (reverse)
				img_src += "&nbsp;&nbsp; <img valign=bottom src="+'"'+"../support/down_sort.gif"+'"'+">";
			else
				img_src += "&nbsp;&nbsp; <img valign=bottom src="+'"'+"../support/up_sort.gif"+'"'+">";
			}

		if (use_dom) 
			{
			document.getElementById(h_name).innerHTML = table.Columns[j].hname + img_src;
			}
		else if (use_css) {
			if(table.Columns[j].type == "form") 
				{
				document.getElementById(h_name).innerHTML = table.Columns[j].hname + img_src;	
				}
			else 
				{
				document.all[h_name].innerHTML = table.Columns[j].hname + img_src;			
				}		
			}
		else if (use_layers) 
			{
			var h_namex= "d"+table.name+"-"+j+"-"+"x";
			if (table.Columns[j].align != '') 
				{
				document.layers[cell_name].document.layers[cell_namex].document.write("<SPAN CLASS=\""+table.Columns[j].align+"\">");
				}
			document.layers[cell_name].document.layers[h_namex].document.write(table.Columns[j].hname + img_src);
								
			if (table.Columns[j].align != '') {
				document.layers[cell_name].document.layers[cell_namex].document.write("</SPAN>");
				}
			document.layers[cell_name].document.layers[cell_namex].document.close();
			}		
		//-----------------------------------



		if (use_dom) {
			if(table.Columns[j].type == "form") 
			   {
			   var iname = table.Lines[i][j].text;
	
			   if (j==0)
				   document.getElementById(cell_name).innerHTML = line_num;
			   else
					{
					document.getElementById(cell_name).innerHTML = tempcolumns[iname];
					}
				}
			else 
			   {
			   if (j==0)
				document.getElementById(cell_name).innerHTML = line_num;
			   else
				{
				document.getElementById(cell_name).innerHTML = table.Lines[i][j].text;	
				}
			  }
			}
		else if (use_css) {
			if(table.Columns[j].type == "form") {
				var iname = table.Lines[i][j].text;

				if (j ==0)
				  document.all[cell_name].innerHTML = line_num;
				else
					{
					document.all[cell_name].innerHTML = tempcolumns[iname];
					}
				}
			else {
				if (j==0)
				  document.all[cell_name].innerHTML = line_num;
				else
					{
					document.all[cell_name].innerHTML = table.Lines[i][j].text;
					}
				}
			}
		else if (use_layers) {
			var cell_namex= "d"+table.name+"-"+i+"-"+j+"x";
			if (table.Columns[j].align != '') {
				document.layers[cell_name].document.layers[cell_namex].document.write("<SPAN CLASS=\""+table.Columns[j].align+"\">");
				}

			if (j==0)
			   document.layers[cell_name].document.layers[cell_namex].document.write(line_num);
			else
				{
				document.layers[cell_name].document.layers[cell_namex].document.write(table.Lines[i][j].text);
				}
			if (table.Columns[j].align != '') {
				document.layers[cell_name].document.layers[cell_namex].document.write("</SPAN>");
				}
			document.layers[cell_name].document.layers[cell_namex].document.close();
			}
			}				
		}
	}

/*-- End of Javasript Table functions --*/



/*---Begin Javascript Popup Functions -----*/
function PopUp(URL, scrollbars, menubar, resizable, width, height) 
{
date = new Date ();
id = date.getTime();

var options = "toolbar=0,scrollbars="+scrollbars+",location=0,statusbar=0,menubar="+menubar+",resizable="+resizable+",width="+width+",height="+height+",left = 362,top = 234";
var new_window = window.open(URL, "", options);
}

// getAnchorPosition(anchorname)
//	 This function returns an object having .x and .y properties which are the coordinates
//	 of the named anchor, relative to the page.
function getAnchorPosition(anchorname) {
	// This function will return an Object with x and y properties
	var useWindow=false;
	var coordinates=new Object();
	var x=0,y=0;
	// Browser capability sniffing
	var use_gebi=false, use_css=false, use_layers=false;
	if (document.getElementById) { use_gebi=true; }
	else if (document.all) { use_css=true; }
	else if (document.layers) { use_layers=true; }
	// Logic to find position
	if (use_gebi && document.all) {
		x=AnchorPosition_getPageOffsetLeft(document.all[anchorname]);
		y=AnchorPosition_getPageOffsetTop(document.all[anchorname]);
		}
	else if (use_gebi) {
		var o=document.getElementById(anchorname);
		x=AnchorPosition_getPageOffsetLeft(o);
		y=AnchorPosition_getPageOffsetTop(o);
		}
	else if (use_css) {
		x=AnchorPosition_getPageOffsetLeft(document.all[anchorname]);
		y=AnchorPosition_getPageOffsetTop(document.all[anchorname]);
		}
	else if (use_layers) {
		var found=0;
		for (var i=0; i<document.anchors.length; i++) {
			if (document.anchors[i].name==anchorname) { found=1; break; }
			}
		if (found==0) {
			coordinates.x=0; coordinates.y=0; return coordinates;
			}
		x=document.anchors[i].x;
		y=document.anchors[i].y;
		}
	else {
		coordinates.x=0; coordinates.y=0; return coordinates;
		}
	coordinates.x=x;
	coordinates.y=y;
	return coordinates;
	}

// getAnchorWindowPosition(anchorname)
//	 This function returns an object having .x and .y properties which are the coordinates
//	 of the named anchor, relative to the window
function getAnchorWindowPosition(anchorname) {
	var coordinates=getAnchorPosition(anchorname);
	var x=0;
	var y=0;
	if (document.getElementById) {
		if (isNaN(window.screenX)) {
			x=coordinates.x-document.body.scrollLeft+window.screenLeft;
			y=coordinates.y-document.body.scrollTop+window.screenTop;
			}
		else {
			x=coordinates.x+window.screenX+(window.outerWidth-window.innerWidth)-window.pageXOffset;
			y=coordinates.y+window.screenY+(window.outerHeight-24-window.innerHeight)-window.pageYOffset;
			}
		}
	else if (document.all) {
		x=coordinates.x-document.body.scrollLeft+window.screenLeft;
		y=coordinates.y-document.body.scrollTop+window.screenTop;
		}
	else if (document.layers) {
		x=coordinates.x+window.screenX+(window.outerWidth-window.innerWidth)-window.pageXOffset;
		y=coordinates.y+window.screenY+(window.outerHeight-24-window.innerHeight)-window.pageYOffset;
		}
	coordinates.x=x;
	coordinates.y=y;
	return coordinates;
	}

// Functions for IE to get position of an object
function AnchorPosition_getPageOffsetLeft (el) {
	var ol=el.offsetLeft;
	while ((el=el.offsetParent) != null) { ol += el.offsetLeft; }
	return ol;
	}
function AnchorPosition_getWindowOffsetLeft (el) {
	return AnchorPosition_getPageOffsetLeft(el)-document.body.scrollLeft;
	}	
function AnchorPosition_getPageOffsetTop (el) {
	var ot=el.offsetTop;
	while((el=el.offsetParent) != null) { ot += el.offsetTop; }
	return ot;
	}
function AnchorPosition_getWindowOffsetTop (el) {
	return AnchorPosition_getPageOffsetTop(el)-document.body.scrollTop;
	}

// Set the position of the popup window based on the anchor
function PopupWindow_getXYPosition(anchorname) {
	var coordinates;
	if (this.type == "WINDOW") {
		coordinates = getAnchorWindowPosition(anchorname);
		}
	else {
		coordinates = getAnchorPosition(anchorname);
		}
	this.x = coordinates.x;
	this.y = coordinates.y;
	}
// Set width/height of DIV/popup window
function PopupWindow_setSize(width,height) {
	this.width = width;
	this.height = height;
	}
// Fill the window with contents
function PopupWindow_populate(contents) {
	this.contents = contents;
	this.populated = false;
	}
// Refresh the displayed contents of the popup
function PopupWindow_refresh() {
	if (this.divName != null) {
		// refresh the DIV object
		if (this.use_gebi) {
			document.getElementById(this.divName).innerHTML = this.contents;
			}
		else if (this.use_css) { 
			document.all[this.divName].innerHTML = this.contents;
			}
		else if (this.use_layers) { 
			var d = document.layers[this.divName]; 
			d.document.open();
			d.document.writeln(this.contents);
			d.document.close();
			}
		}
	else {
		if (this.popupWindow != null && !this.popupWindow.closed) {
			this.popupWindow.document.open();
			this.popupWindow.document.writeln(this.contents);
			this.popupWindow.document.close();
			this.popupWindow.focus();
			}
		}
	}
// Position and show the popup, relative to an anchor object
function PopupWindow_showPopup(anchorname) {
	this.getXYPosition(anchorname);
	this.x += this.offsetX;
	this.y += this.offsetY;
	if (!this.populated && (this.contents != "")) {
		this.populated = true;
		this.refresh();
		}
	if (this.divName != null) {
		// Show the DIV object
		if (this.use_gebi) {
			document.getElementById(this.divName).style.left = this.x;
			document.getElementById(this.divName).style.top = this.y;
			document.getElementById(this.divName).style.visibility = "visible";
			}
		else if (this.use_css) {
			document.all[this.divName].style.left = this.x;
			document.all[this.divName].style.top = this.y;
			document.all[this.divName].style.visibility = "visible";
			}
		else if (this.use_layers) {
			document.layers[this.divName].left = this.x;
			document.layers[this.divName].top = this.y;
			document.layers[this.divName].visibility = "visible";
			}
		}
	else {
		if (this.popupWindow == null || this.popupWindow.closed) {
			// If the popup window will go off-screen, move it so it doesn't
			if (screen && screen.availHeight) {
				if ((this.y + this.height) > screen.availHeight) {
					this.y = screen.availHeight - this.height;
					}
				}
			if (screen && screen.availWidth) {
				if ((this.x + this.width) > screen.availWidth) {
					this.x = screen.availWidth - this.width;
					}
				}
			this.popupWindow = window.open("about:blank","window_"+anchorname,"toolbar=no,location=no,status=no,menubar=no,scrollbars=auto,resizable=yes,alwaysRaised,dependent,titlebar=no,width="+this.width+",height="+this.height+",screenX="+this.x+",left="+this.x+",screenY="+this.y+",top="+this.y+"");
			}
		this.refresh();
		}
	}
// Hide the popup
function PopupWindow_hidePopup() {
	if (this.divName != null) {
		if (this.use_gebi) {
			document.getElementById(this.divName).style.visibility = "hidden";
			}
		else if (this.use_css) {
			document.all[this.divName].style.visibility = "hidden";
			}
		else if (this.use_layers) {
			document.layers[this.divName].visibility = "hidden";
			}
		}
	else {
		if (this.popupWindow && !this.popupWindow.closed) {
			this.popupWindow.close();
			this.popupWindow = null;
			}
		}
	}
// Pass an event and return whether or not it was the popup DIV that was clicked
function PopupWindow_isClicked(e) {
	if (this.divName != null) {
		if (this.use_layers) {
			var clickX = e.pageX;
			var clickY = e.pageY;
			var t = document.layers[this.divName];
			if ((clickX > t.left) && (clickX < t.left+t.clip.width) && (clickY > t.top) && (clickY < t.top+t.clip.height)) {
				return true;
				}
			else { return false; }
			}
		else if (document.all) { // Need to hard-code this to trap IE for error-handling
			var t = window.event.srcElement;
			while (t.parentElement != null) {
				if (t.id==this.divName) {
					return true;
					}
				t = t.parentElement;
				}
			return false;
			}
		else if (this.use_gebi) {
			var t = e.originalTarget;
			while (t.parentNode != null) {
				if (t.id==this.divName) {
					return true;
					}
				t = t.parentNode;
				}
			return false;
			}
		return false;
		}
	return false;
	}

// Check an onMouseDown event to see if we should hide
function PopupWindow_hideIfNotClicked(e) {
	if (this.autoHideEnabled && !this.isClicked(e)) {
		this.hidePopup();
		}
	}
// Call this to make the DIV disable automatically when mouse is clicked outside it
function PopupWindow_autoHide() {
	this.autoHideEnabled = true;
	}
// This global function checks all PopupWindow objects onmouseup to see if they should be hidden
function PopupWindow_hidePopupWindows(e) {
	for (var i=0; i<popupWindowObjects.length; i++) {
		if (popupWindowObjects[i] != null) {
			var p = popupWindowObjects[i];
			p.hideIfNotClicked(e);
			}
		}
	}
// Run this immediately to attach the event listener
function PopupWindow_attachListener() {
	if (document.layers) {
		document.captureEvents(Event.MOUSEUP);
		}
	window.popupWindowOldEventListener = document.onmouseup;
	if (window.popupWindowOldEventListener != null) {
		document.onmouseup = new Function("window.popupWindowOldEventListener(); PopupWindow_hidePopupWindows();");
		}
	else {
		document.onmouseup = PopupWindow_hidePopupWindows;
		}
	}
// CONSTRUCTOR for the PopupWindow object
// Pass it a DIV name to use a DHTML popup, otherwise will default to window popup
function PopupWindow() {
	if (!window.popupWindowIndex) { window.popupWindowIndex = 0; }
	if (!window.popupWindowObjects) { window.popupWindowObjects = new Array(); }
	if (!window.listenerAttached) {
		window.listenerAttached = true;
		PopupWindow_attachListener();
		}
	this.index = popupWindowIndex++;
	popupWindowObjects[this.index] = this;
	this.divName = null;
	this.popupWindow = null;
	this.width=0;
	this.height=0;
	this.populated = false;
	this.visible = false;
	this.autoHideEnabled = false;
	
	this.contents = "";
	if (arguments.length>0) {
		this.type="DIV";
		this.divName = arguments[0];
		}
	else {
		this.type="WINDOW";
		}
	this.use_gebi = false;
	this.use_css = false;
	this.use_layers = false;
	if (document.getElementById) { this.use_gebi = true; }
	else if (document.all) { this.use_css = true; }
	else if (document.layers) { this.use_layers = true; }
	else { this.type = "WINDOW"; }
	this.offsetX = 0;
	this.offsetY = 0;
	// Method mappings
	this.getXYPosition = PopupWindow_getXYPosition;
	this.populate = PopupWindow_populate;
	this.refresh = PopupWindow_refresh;
	this.showPopup = PopupWindow_showPopup;
	this.hidePopup = PopupWindow_hidePopup;
	this.setSize = PopupWindow_setSize;
	this.isClicked = PopupWindow_isClicked;
	this.autoHide = PopupWindow_autoHide;
	this.hideIfNotClicked = PopupWindow_hideIfNotClicked;
	}

/*-- End of Javasript Popup functions --*/


/* -- Begin Table Row Selection Functions --*/
// -------------------------------------------------------------------
// selectUnselectMatchingOptions(select_object,regex,select/unselect,true/false)
//	This is a general function used by the select functions below, to
//	avoid code duplication
// -------------------------------------------------------------------
function selectUnselectMatchingOptions(obj,regex,which,only) {
	if (window.RegExp) {
		if (which == "select") {
			var selected1=true;
			var selected2=false;
			}
		else if (which == "unselect") {
			var selected1=false;
			var selected2=true;
			}
		else {
			return;
			}
		var re = new RegExp(regex);
		for (var i=0; i<obj.options.length; i++) {
			if (re.test(obj.options[i].text)) {
				obj.options[i].selected = selected1;
				}
			else {
				if (only == true) {
					obj.options[i].selected = selected2;
					}
				}
			}
		}
	}
		
// -------------------------------------------------------------------
// selectMatchingOptions(select_object,regex)
//	This function selects all options that match the regular expression
//	passed in. Currently-selected options will not be changed.
// -------------------------------------------------------------------
function selectMatchingOptions(obj,regex) {
	selectUnselectMatchingOptions(obj,regex,"select",false);
	}
// -------------------------------------------------------------------
// selectOnlyMatchingOptions(select_object,regex)
//	This function selects all options that match the regular expression
//	passed in. Selected options that don't match will be un-selected.
// -------------------------------------------------------------------
function selectOnlyMatchingOptions(obj,regex) {
	selectUnselectMatchingOptions(obj,regex,"select",true);
	}
// -------------------------------------------------------------------
// unSelectMatchingOptions(select_object,regex)
//	This function Unselects all options that match the regular expression
//	passed in. 
// -------------------------------------------------------------------
function unSelectMatchingOptions(obj,regex) {
	selectUnselectMatchingOptions(obj,regex,"unselect",false);
	}
	
// -------------------------------------------------------------------
// sortSelect(select_object)
//	 Pass this function a SELECT object and the options will be sorted
//	 by their text (display) values
// -------------------------------------------------------------------
function sortSelect(obj) {
	var o = new Array();
	if (obj.options==null) { return; }
	for (var i=0; i<obj.options.length; i++) {
		o[o.length] = new Option( obj.options[i].text, obj.options[i].value, obj.options[i].defaultSelected, obj.options[i].selected) ;
		}
	if (o.length==0) { return; }
	o = o.sort( 
		function(a,b) { 
			if ((a.text+"") < (b.text+"")) { return -1; }
			if ((a.text+"") > (b.text+"")) { return 1; }
			return 0;
			} 
		);

	for (var i=0; i<o.length; i++) {
		obj.options[i] = new Option(o[i].text, o[i].value, o[i].defaultSelected, o[i].selected);
		}
	}

// -------------------------------------------------------------------
// selectAllOptions(select_object)
//	This function takes a select box and selects all options (in a 
//	multiple select object). This is used when passing values between
//	two select boxes. Select all options in the right box before 
//	submitting the form so the values will be sent to the server.
// -------------------------------------------------------------------
function selectAllOptions(obj) {
	for (var i=0; i<obj.options.length; i++) {
		obj.options[i].selected = true;
		}
	}
	
// -------------------------------------------------------------------
// moveSelectedOptions(select_object,select_object[,autosort(true/false)[,regex]])
//	This function moves options between select boxes. Works best with
//	multi-select boxes to create the common Windows control effect.
//	Passes all selected values from the first object to the second
//	object and re-sorts each box.
//	If a third argument of 'false' is passed, then the lists are not
//	sorted after the move.
//	If a fourth string argument is passed, this will function as a
//	Regular Expression to match against the TEXT or the options. If 
//	the text of an option matches the pattern, it will NOT be moved.
//	It will be treated as an unmoveable option.
//	You can also put this into the <SELECT> object as follows:
//	  onDblClick="moveSelectedOptions(this,this.form.target)
//	This way, when the user double-clicks on a value in one box, it
//	will be transferred to the other (in browsers that support the 
//	onDblClick() event handler).
// -------------------------------------------------------------------
function moveSelectedOptions(from,to) {
	// Unselect matching options, if required
	if (arguments.length>3) {
		var regex = arguments[3];
		if (regex != "") {
			unSelectMatchingOptions(from,regex);
			}
		}
	// Move them over
	for (var i=0; i<from.options.length; i++) {
		var o = from.options[i];
		if (o.selected) {
			to.options[to.options.length] = new Option( o.text, o.value, false, false);
			}
		}
	// Delete them from original
	for (var i=(from.options.length-1); i>=0; i--) {
		var o = from.options[i];
		if (o.selected) {
			from.options[i] = null;
			}
		}
	if ((arguments.length<3) || (arguments[2]==true)) {
		sortSelect(from);
		sortSelect(to);
		}
	from.selectedIndex = -1;
	to.selectedIndex = -1;
	}

// -------------------------------------------------------------------
// copySelectedOptions(select_object,select_object[,autosort(true/false)])
//	This function copies options between select boxes instead of 
//	moving items. Duplicates in the target list are not allowed.
// -------------------------------------------------------------------
function copySelectedOptions(from,to) {
	var options = new Object();
	for (var i=0; i<to.options.length; i++) {
		options[to.options[i].text] = true;
		}
	for (var i=0; i<from.options.length; i++) {
		var o = from.options[i];
		if (o.selected) {
			if (options[o.text] == null || options[o.text] == "undefined") {
				to.options[to.options.length] = new Option( o.text, o.value, false, false);
				}
			}
		}
	if ((arguments.length<3) || (arguments[2]==true)) {
		sortSelect(to);
		}
	from.selectedIndex = -1;
	to.selectedIndex = -1;
	}

// -------------------------------------------------------------------
// moveAllOptions(select_object,select_object[,autosort(true/false)[,regex]])
//	Move all options from one select box to another.
// -------------------------------------------------------------------
function moveAllOptions(from,to) {
	selectAllOptions(from);
	if (arguments.length==2) {
		moveSelectedOptions(from,to);
		}
	else if (arguments.length==3) {
		moveSelectedOptions(from,to,arguments[2]);
		}
	else if (arguments.length==4) {
		moveSelectedOptions(from,to,arguments[2],arguments[3]);
		}
	}

// -------------------------------------------------------------------
// copyAllOptions(select_object,select_object[,autosort(true/false)])
//	Copy all options from one select box to another, instead of
//	removing items. Duplicates in the target list are not allowed.
// -------------------------------------------------------------------
function copyAllOptions(from,to) {
	selectAllOptions(from);
	if (arguments.length==2) {
		copySelectedOptions(from,to);
		}
	else if (arguments.length==3) {
		copySelectedOptions(from,to,arguments[2]);
		}
	}

// -------------------------------------------------------------------
// swapOptions(select_object,option1,option2)
//	Swap positions of two options in a select list
// -------------------------------------------------------------------
function swapOptions(obj,i,j) {
	var o = obj.options;
	var i_selected = o[i].selected;
	var j_selected = o[j].selected;
	var temp = new Option(o[i].text, o[i].value, o[i].defaultSelected, o[i].selected);
	var temp2= new Option(o[j].text, o[j].value, o[j].defaultSelected, o[j].selected);
	o[i] = temp2;
	o[j] = temp;
	o[i].selected = j_selected;
	o[j].selected = i_selected;
	}
	
// -------------------------------------------------------------------
// moveOptionUp(select_object)
//	Move selected option in a select list up one
// -------------------------------------------------------------------
function moveOptionUp(obj) {
	// If > 1 option selected, do nothing
	var selectedCount=0;
	for (i=0; i<obj.options.length; i++) {
		if (obj.options[i].selected) {
			selectedCount++;
			}
		}
	if (selectedCount!=1) {
		return;
		}
	// If this is the first item in the list, do nothing
	var i = obj.selectedIndex;
	if (i == 0) {
		return;
		}
	swapOptions(obj,i,i-1);
	obj.options[i-1].selected = true;
	}

// -------------------------------------------------------------------
// moveOptionDown(select_object)
//	Move selected option in a select list down one
// -------------------------------------------------------------------
function moveOptionDown(obj) {
	// If > 1 option selected, do nothing
	var selectedCount=0;
	for (i=0; i<obj.options.length; i++) {
		if (obj.options[i].selected) {
			selectedCount++;
			}
		}
	if (selectedCount != 1) {
		return;
		}
	// If this is the last item in the list, do nothing
	var i = obj.selectedIndex;
	if (i == (obj.options.length-1)) {
		return;
		}
	swapOptions(obj,i,i+1);
	obj.options[i+1].selected = true;
	}

// -------------------------------------------------------------------
// removeSelectedOptions(select_object)
//	Remove all selected options from a list
//	(Thanks to Gene Ninestein)
// -------------------------------------------------------------------
function removeSelectedOptions(from) { 
	for (var i=(from.options.length-1); i>=0; i--) { 
		var o=from.options[i]; 
		if (o.selected) { 
			from.options[i] = null; 
			} 
		} 
	from.selectedIndex = -1; 
	} 

function OT_transferLeft() { moveSelectedOptions(this.right,this.left,this.autoSort); this.update(); }
function OT_transferRight() { moveSelectedOptions(this.left,this.right,this.autoSort); this.update();}


function OT_transferAllLeft() { moveAllOptions(this.right,this.left,this.autoSort); this.update(); }
function OT_transferAllRight() { moveAllOptions(this.left,this.right,this.autoSort); this.update(); }
function OT_saveRemovedLeftOptions(f) { this.removedLeftField = f; }
function OT_saveRemovedRightOptions(f) { this.removedRightField = f; }
function OT_saveAddedLeftOptions(f) { this.addedLeftField = f; }
function OT_saveAddedRightOptions(f) { this.addedRightField = f; }
function OT_saveNewLeftOptions(f) { this.newLeftField = f; }
function OT_saveNewRightOptions(f) { this.newRightField = f; }
function OT_update() {
	var removedLeft = new Object();
	var removedRight = new Object();
	var addedLeft = new Object();
	var addedRight = new Object();
	var newLeft = new Object();
	var newRight = new Object();
	for (var i=0;i<this.left.options.length;i++) {
		var o=this.left.options[i];
		newLeft[o.value]=1;
		if (typeof(this.originalLeftValues[o.value])=="undefined") {
			addedLeft[o.value]=1;
			removedRight[o.value]=1;
			}
		}
	for (var i=0;i<this.right.options.length;i++) {
		var o=this.right.options[i];
		newRight[o.value]=1;
		if (typeof(this.originalRightValues[o.value])=="undefined") {
			addedRight[o.value]=1;
			removedLeft[o.value]=1;
			}
		}
	if (this.removedLeftField!=null) { this.removedLeftField.value = OT_join(removedLeft,this.delimiter); }
	if (this.removedRightField!=null) { this.removedRightField.value = OT_join(removedRight,this.delimiter); }
	if (this.addedLeftField!=null) { this.addedLeftField.value = OT_join(addedLeft,this.delimiter); }
	if (this.addedRightField!=null) { this.addedRightField.value = OT_join(addedRight,this.delimiter); }
	if (this.newLeftField!=null) { this.newLeftField.value = OT_join(newLeft,this.delimiter); }
	if (this.newRightField!=null) { this.newRightField.value = OT_join(newRight,this.delimiter); }
	}
function OT_join(o,delimiter) {
	var val; var str="";
	for(val in o){
		if (str.length>0) { str=str+delimiter; }
		str=str+val;
		}
	return str;
	}
function OT_setDelimiter(val) { this.delimiter=val; }
function OT_setAutoSort(val) { this.autoSort=val; }
function OT_init(theform) {
	this.form = theform;
	if(!theform[this.left]){alert("OptionTransfer init(): Left select list does not exist in form!");return false;}
	if(!theform[this.right]){alert("OptionTransfer init(): Right select list does not exist in form!");return false;}
	this.left=theform[this.left];
	this.right=theform[this.right];
	for(var i=0;i<this.left.options.length;i++) {
		this.originalLeftValues[this.left.options[i].value]=1;
		}
	for(var i=0;i<this.right.options.length;i++) {
		this.originalRightValues[this.right.options[i].value]=1;
		}
	if(this.removedLeftField!=null) { this.removedLeftField=theform[this.removedLeftField]; }
	if(this.removedRightField!=null) { this.removedRightField=theform[this.removedRightField]; }
	if(this.addedLeftField!=null) { this.addedLeftField=theform[this.addedLeftField]; }
	if(this.addedRightField!=null) { this.addedRightField=theform[this.addedRightField]; }
	if(this.newLeftField!=null) { this.newLeftField=theform[this.newLeftField]; }
	if(this.newRightField!=null) { this.newRightField=theform[this.newRightField]; }
	this.update();
	}
// -------------------------------------------------------------------
// OptionTransfer()
//	This is the object interface.
// -------------------------------------------------------------------
function OptionTransfer(l,r) {
	this.form = null;
	this.left=l;
	this.right=r;
	this.autoSort=true;
	this.delimiter=",";
	this.originalLeftValues = new Object();
	this.originalRightValues = new Object();
	this.removedLeftField = null;
	this.removedRightField = null;
	this.addedLeftField = null;
	this.addedRightField = null;
	this.newLeftField = null;
	this.newRightField = null;
	this.transferLeft=OT_transferLeft;
	this.transferRight=OT_transferRight;
	this.transferAllLeft=OT_transferAllLeft;
	this.transferAllRight=OT_transferAllRight;
	this.saveRemovedLeftOptions=OT_saveRemovedLeftOptions;
	this.saveRemovedRightOptions=OT_saveRemovedRightOptions;
	this.saveAddedLeftOptions=OT_saveAddedLeftOptions;
	this.saveAddedRightOptions=OT_saveAddedRightOptions;
	this.saveNewLeftOptions=OT_saveNewLeftOptions;
	this.saveNewRightOptions=OT_saveNewRightOptions;
	this.setDelimiter=OT_setDelimiter;
	this.setAutoSort=OT_setAutoSort;
	this.init=OT_init;
	this.update=OT_update;
	}

/* -- End of Table Row Selection Functions --*/

function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
	var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
	if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_findObj(n, d) { //v3.0
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
	d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}


/* ---------------- Bar Chart Code -------------------------*/
function CreateSingleBarSimple(cur_value, chartWidth, maxVal, over_color)
	{

	if (!IsNumeric (cur_value))
		return cur_value;

	imRed  = "../support/red_bar.gif";
	imYellow  = "../support/yellow_bar.gif";
	imGreen  = "../support/green_bar.gif";
	imBlack  = "../support/black_bar.gif";
	imBlue  = "../support/blue_bar.gif";	
	imGrey  = "../support/grey_bar.gif";
	imOrange  = "../support/orange_bar.gif";

	strToWrite = "";  // We'll dynamically build a table to hold
				  // our chart, then write it to the screen when ready

	
	var color;
	
	if (over_color == "na")
		{
		if (cur_value > 75)
			color = imRed;
		else if (cur_value>25 && cur_value<75)
			color = imOrange;
		else if (cur_value>5 && cur_value<25)
			color = imGreen;
		else 
			color = imBlue;
		}
	else
		{
		if (over_color == "blue")
			color = imBlue;
		else if (over_color == "red")
			color = imRed;
		else if (over_color == "green")
			color = imGreen;
		else if (over_color == "orange")
			color = imOrange;
		else if (over_color == "grey")
			color = imGrey;
		else if (over_color == "yellow")
			color = imYellow;
		else if (over_color == "black")
			color = imBlack;
		}

	strToWrite += "<img src='" + color + "' width=" + parseInt((cur_value/maxVal) * chartWidth) + " height=10 >";			
	strToWrite += "   "+ cur_value;

	return strToWrite;
	}


function CreateSingleBar (cur_value, threshold, chartWidth, maxVal, value_str)
	{

	if (!IsNumeric (cur_value))
		return cur_value;

	imRed  = "../support/red_bar.gif";
	imYellow  = "../support/yellow_bar.gif";
	imGreen  = "../support/green_bar.gif";
	imBlack  = "../support/black_bar.gif";
	imBlue  = "../support/blue_bar.gif";	
	imGrey  = "../support/grey_bar.gif";
	imOrange  = "../support/orange_bar.gif";

	strToWrite = "";  // We'll dynamically build a table to hold
				  // our chart, then write it to the screen when ready

	//normilize values and threshold to 100% scale

	if (threshold > maxVal) maxVal=threshold;
	
	orig_thresh = threshold;

	threshold = (threshold/maxVal) * chartWidth;
	cur_value = (cur_value/maxVal) * chartWidth;

	if (cur_value < 1)	cur_value = 1;
	if (cur_value > chartWidth) cur_value = chartWidth;
	if (threshold < 1) threshold = 1;
	if (threshold > chartWidth) threshold = chartWidth;

	if (cur_value > threshold)
		{
		max_right = chartWidth - cur_value;
		extra = cur_value - threshold;
		min_left = threshold;
		min_left_color = imRed;
		max_left = -1;
		}
	else
		{
		diff = (cur_value/threshold) * 100;

		min_left = cur_value;
		max_left = threshold - cur_value;

		if (diff >= 100)
			min_left_color = imRed;
		if (diff >= 75 && diff < 100 || cur_value==threshold)
			min_left_color = imOrange;
		else if (diff >= 35 && diff < 75)
			min_left_color = imYellow;
		else if (diff >=1 && diff < 35)
			min_left_color = imGreen;
		else
			min_left_color = imBlue;
		
		extra = -1;
		max_right = chartWidth - threshold;
		}


	strToWrite += "<img src='" + min_left_color + "' width=" + parseInt(min_left) + " height=10 >";
	
	if (max_left > 0)
		strToWrite += "<img src='" + imGrey + "' width=" + parseInt(max_left) + " height=10 >";			
	
	strToWrite += "<img src='" + imBlack + "' width=" + parseInt(3) + " height=10 >";

	if (extra> 0)
		strToWrite += "<img src='" + imRed + "' width=" + parseInt(extra) + " height=10 >";
	
	if (max_right > 0)
		strToWrite += "<img src='" + imGrey + "' width=" + parseInt(max_right) + " height=10 >";

	strToWrite += "<b>"+ value_str +"</b>";

	return strToWrite;
	}

	
	function IsNumeric(strString)
   //  check for valid numeric strings	
   {
   var strValidChars = "0123456789.,-";
   var strChar;
   var blnResult = true;

   if (strString.length == 0) return false;

   //  test strString consists of valid characters listed above
   for (i = 0; i < strString.length && blnResult == true; i++)
      {
      strChar = strString.charAt(i);
      if (strValidChars.indexOf(strChar) == -1)
         {
         blnResult = false;
         }
      }
   return blnResult;
   }


function DisableThresholdField ()
	{
	var sort_type = document.SortForm.type.value;
	if (sort_type == 0)
		document.SortForm.thresh.disabled=true;
	else
		document.SortForm.thresh.disabled=false;
	}


function removestr(s, rs) 
{
//Remove a given character from the string
// returns a new string
	r = "";
	for (i=0; i < s.length; i++) 
	{
	 if (s.charAt(i) != rs) 
		{
		r += s.charAt(i);
		}
	}
return r;
}




//+-+-+-+-+-+-+-+-+-+-+-+-+for highlight row+-+-+-+-+-+-+-+-+-+-+-+-+//
//desc: Highligt current row by click any cell
function HighLightTR(odd)
{  
	var el = event.srcElement;
	el = el.parentElement;

	if (el.bgColor !="#ccccff")
		el.bgColor ="#ccccff";
	else
		{
		if (odd == 0)
			el.bgColor ="#cccccc";
		else
			el.bgColor ="#ffffff";
		}

	try
	{
	ChangeTextColor(el, "#000000");
	}catch(e){;}	 
}

function ChangeTextColor(a_obj,a_color)
{
for (i=0;i<a_obj.cells.length;i++)
	{
	a_obj.cells(i).style.color=a_color; 
	}
}




