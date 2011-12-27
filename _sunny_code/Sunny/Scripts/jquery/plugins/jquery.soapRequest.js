/*==========================
jquery.soapRequest.js
communicating with soap

This script is basically a wrapper for jqSOAPClient.beta.js from proton17

I only fixed a minor bug, and added two functions:
One function to send the soapRequest that takes a complex object as a parameter
and that deals with the response so you can set actions for success or error.
Also I made a very basic json2soap function.
(at the moment it will not deal with arrays properly)
After that I wrapped it all to become a proper jQuery plugin so you can call:

	$.soapRequest({
		url: 'http://my.server.com/soapservices/',
		method: 'helloWorld',
		params: {
			name: 'Remy Blom',
			msg: 'Hi!'
		},
		returnJson: false,  // default is false, so it won't need dependencies
		success: function (data) {
			// do stuff with data
		},
		error: function (string) {
			// show error
		}
	});
	
Dependencies: 
If you want the function to return json (ie. convert the response soap/xml to json)
you will need the jQuery.xml2json.js 

created at: Dec 03, 2009
scripted by: 

Remy Blom,
Utrecht School of Arts,
The Netherlands

www.hku.nl
remy.blom@kmt.hku.nl
==========================*/

(function($) {
	$.soapRequest = function(soapRequestObject) {
		var config = {
			returnJson: false
		};
		if (soapRequestObject) $.extend(config, soapRequestObject);
		
		var mySoapObject = json2soap(new SOAPObject(soapRequestObject.method), soapRequestObject.params)
		var soapRequest = new SOAPRequest(null, mySoapObject); 
		
		SOAPClient.Proxy = soapRequestObject.url + soapRequestObject.method; 
		SOAPClient.SendRequest(soapRequest, function (data) {
			if(config.returnJson) {
				var jdata = $.xml2json(data);
				if ((jdata.Body) && (jdata.Body.Fault)) soapRequestObject.error(jdata.Body.Fault.faultstring);
				else if (jdata.Body) soapRequestObject.success(jdata.Body);
				else soapRequestObject.error('Unexpected data received');
			} else {
				if ($(data).find('faultstring').length > 0) soapRequestObject.error($(data).find('faultstring'));
				else if (data.documentElement.localName == 'Envelope') soapRequestObject.success(data);
				else soapRequestObject.error('Unexpected data received');
			}
		});
	}
	var json2soap = function (soapObject, params) {
		for (x in params) {
			if (typeof params[x] == 'object') {
				myParam = json2soap(new SOAPObject(x), params[x]);
				soapObject.appendChild(myParam);
			} else {
				soapObject.addParameter(x, params[x]);
			}
		}
		return soapObject;
	}
	
/*
All code below this point is proton17's

		ORIGINAL LICENSE:

		This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/	
	
	var SOAPClient = (function() {
		var httpHeaders = {};
		var _tId = null;
		var _self = {
			Proxy: "",
			SOAPServer: "",
			ContentType: "text/xml",
			CharSet: "utf-8",
			ResponseXML: null,
			ResponseText: "",
			Status: 0,
			ContentLength: 0,
			Timeout: 0,
			SetHTTPHeader: function(name, value){
				var re = /^[\w]{1,20}$/;
				if((typeof(name) === "string") && re.test(name)) {
					httpHeaders[name] = value;
				} 
			},
			Namespace: function(name, uri) {
				return {"name":name, "uri":uri};
			},
			SendRequest: function(soapReq, callback) {		
				if(!!SOAPClient.Proxy) {
					SOAPClient.ResponseText = "";
					SOAPClient.ResponseXML = null;
					SOAPClient.Status = 0;
					
					var content = soapReq.toString();
					SOAPClient.ContentLength = content.length;
					
					function getResponse(xData) {
						if(!!_tId) {clearTimeout(_tId);}
							SOAPClient.Status = xhrReq.status;
							SOAPClient.ResponseText = xhrReq.responseText;
							SOAPClient.ResponseXML = xhrReq.responseXML;
						if(typeof(callback) === "function") {
							callback(xData.responseXML);
						}
					}
					var xhrReq = $.ajax({
						 type: "POST",
						 url: SOAPClient.Proxy,
						 dataType: "xml",
						 processData: false,
						 data: content,
						 complete: getResponse,
						 contentType: SOAPClient.ContentType + "; charset=\"" + SOAPClient.CharSet + "\"",
						 beforeSend: function(req) {						
							req.setRequestHeader("Method", "POST");
							req.setRequestHeader("Content-Length", SOAPClient.ContentLength);					
							req.setRequestHeader("SOAPServer", SOAPClient.SOAPServer);
							req.setRequestHeader("SOAPAction", soapReq.Action);
							if(!!httpHeaders) {
								var hh = null, ch = null;
								for(hh in httpHeaders) {
									if (!httpHeaders.hasOwnProperty || httpHeaders.hasOwnProperty(hh)) {
										ch = httpHeaders[hh];
										req.setRequestHeader(hh, ch.value);
									}
								}
							}						
						 }
					});
				}
			},	
			ToXML: function(soapObj) {
				var out = [];
				var isNSObj=false;
				try {
					if(!!soapObj&&typeof(soapObj)==="object"&&soapObj.typeOf==="SOAPObject") {
						//Namespaces
						if(!!soapObj.ns) {
							if(typeof(soapObj.ns)==="object") {
								isNSObj=true;
								out.push("<"+soapObj.ns.name+":"+soapObj.name);
								out.push(" xmlns:"+soapObj.ns.name+"=\""+soapObj.ns.uri+"\"");
							} else  {
								out.push("<"+soapObj.name);
								out.push(" xmlns=\""+soapObj.ns+"\"");
							}
						} else {
							out.push("<"+soapObj.name);
						}
						//Node Attributes
						if(soapObj.attributes.length > 0) {
							 var cAttr;
							 var aLen=soapObj.attributes.length-1;
							 do {
								 cAttr=soapObj.attributes[aLen];
								 if(isNSObj) {
									out.push(" "+soapObj.ns.name+":"+cAttr.name+"=\""+cAttr.value+"\"");
								 } else {
									out.push(" "+cAttr.name+"=\""+cAttr.value+"\"");
								 }
							 } while(aLen--);					 					 
						}
						out.push(">");
						//Node children
						if(soapObj.hasChildren()) {					
							var cPos, cObj;
							for(cPos in soapObj.children){
								cObj = soapObj.children[cPos];
								if(typeof(cObj)==="object"){out.push(SOAPClient.ToXML(cObj));}
							}
						}
						//Node Value
						if(!!soapObj.value){out.push(soapObj.value);}
						//Close Tag
						if(isNSObj){out.push("</"+soapObj.ns.name+":"+soapObj.name+">");}
						else {out.push("</"+soapObj.name+">");}
						return out.join("");
					}
				} catch(e){alert("Unable to process SOAPObject! Object must be an instance of SOAPObject");}
			}
		};
		return _self;
	})();
	//Soap request - this is what being sent using SOAPClient.SendRequest
	var SOAPRequest=function(action, soapObj) {
		this.Action=action;	
		var nss=[];
		var headers=[];
		var bodies=(!!soapObj)?[soapObj]:[];
		this.addNamespace=function(ns, uri){nss.push(new SOAPClient.Namespace(ns, uri));};
		this.addHeader=function(soapObj){headers.push(soapObj);};
		this.addBody=function(soapObj){bodies.push(soapObj);};
		this.toString=function() {
			var soapEnv = new SOAPObject("soap:Envelope");
				soapEnv.attr("xmlns:soap","http://schemas.xmlsoap.org/soap/envelope/");
				soapEnv.attr("soap:encodingStyle","http://schemas.xmlsoap.org/soap/encoding/");
				soapEnv.attr("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
				soapEnv.attr("xmlns:xsd","http://www.w3.org/2001/XMLSchema");
			//Add Namespace(s)
			if(nss.length>0){
				var tNs, tNo;
				for(tNs in nss){if(!nss.hasOwnProperty || nss.hasOwnProperty(tNs)){tNo=nss[tNs];if(typeof(tNo)==="object"){soapEnv.attr("xmlns:"+tNo.name, tNo.uri);}}}
			}
			//Add Header(s)
			if(headers.length>0) {
				var soapHeader = soapEnv.appendChild(new SOAPObject("soap:Header"));
				var tHdr;
				for(tHdr in headers){if(!headers.hasOwnProperty || headers.hasOwnProperty(tHdr)){soapHeader.appendChild(headers[tHdr]);}}
			}
			//Add Body(s)
			if(bodies.length>0) {
				var soapBody = soapEnv.appendChild(new SOAPObject("soap:Body"));
				var tBdy;
				for(tBdy in bodies){if(!bodies.hasOwnProperty || bodies.hasOwnProperty(tBdy)){soapBody.appendChild(bodies[tBdy]);}}
			}
			return soapEnv.toString();		
		};
	};
	
	//Soap Object - Used to build body envelope and other structures
	var SOAPObject = function(name) {
		this.typeOf="SOAPObject";
		this.ns=null;
		this.name=name;
		this.attributes=[];
		this.children=[];
		this.value=null;
		this.attr=function(name, value){this.attributes.push({"name":name, "value":value});return this;};
		this.appendChild=function(obj){this.children.push(obj);return obj;};
		this.addParameter=function(name,value){var obj=new SOAPObject(name);obj.val(value);this.appendChild(obj);};
		this.hasChildren=function(){return (this.children.length > 0)?true:false;};
		this.val=function(v){if(!v){return this.value;}else{this.value=v;return this;}};
		this.toString=function(){return SOAPClient.ToXML(this);};
	};

})(jQuery);

