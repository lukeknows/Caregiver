const urlBase = 'http://localhost';
const extension = 'php';

let userId = 0;
let contactID = -1;

let firstName = "";
let lastName = "";

let pageNum = 0;
let searchText = "";
let returnCount = 0;

function doLogin()
{
	userId = 0;
	firstName = "";
	lastName = "";
	
	let login = document.getElementById("loginName").value;
	let password = document.getElementById("loginPassword").value;
//	var hash = md5( password );
	
	document.getElementById("loginResult").innerHTML = "";

	let tmp = {login:login,password:password};
//	var tmp = {login:login,password:hash};
	let jsonPayload = JSON.stringify( tmp );
	
	let url = urlBase + '/Login.' + extension;

	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
		xhr.onreadystatechange = function() 
		{
			if (this.readyState == 4 && this.status == 200) 
			{
				let jsonObject = JSON.parse( xhr.responseText );
				userId = jsonObject.id;
		
				if( userId < 1 )
				{		
					document.getElementById("loginResult").innerHTML = "User/Password combination incorrect";
					return;
				}
		
				firstName = jsonObject.firstName;
				lastName = jsonObject.lastName;

				saveCookie();
	
				window.location.href = "contacts.html";
			}
		};
		xhr.send(jsonPayload);
	}
	catch(err)
	{
		document.getElementById("loginResult").innerHTML = err.message;
	}

}

function saveCookie()
{
	let minutes = 20;
	let date = new Date();
	date.setTime(date.getTime()+(minutes*60*1000));	
	document.cookie = "firstName=" + firstName + ",lastName=" + lastName + ",userId=" + userId + ";expires=" + date.toGMTString();
}

function readCookie()
{
	userId = -1;
	let data = document.cookie;
	let splits = data.split(",");
	for(var i = 0; i < splits.length; i++) 
	{
		let thisOne = splits[i].trim();
		let tokens = thisOne.split("=");
		if( tokens[0] == "firstName" )
		{
			firstName = tokens[1];
		}
		else if( tokens[0] == "lastName" )
		{
			lastName = tokens[1];
		}
		else if( tokens[0] == "userId" )
		{
			userId = parseInt( tokens[1].trim() );
		}
	}
	
	if( userId < 0 )
	{
		window.location.href = "index.html";
	}
	else
	{
		document.getElementById("userName").innerHTML = "Logged in as " + firstName + " " + lastName;
	}
}

function doLogout()
{
	userId = 0;
	firstName = "";
	lastName = "";
	document.cookie = "firstName= ; expires = Thu, 01 Jan 1970 00:00:00 GMT";
	window.location.href = "index.html";
}

function addColor()
{
	let newColor = document.getElementById("colorText").value;
	document.getElementById("colorAddResult").innerHTML = "";

	let tmp = {color:newColor,userId:userId};
	let jsonPayload = JSON.stringify( tmp );

	let url = urlBase + '/AddColor.' + extension;
	
	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
		xhr.onreadystatechange = function() 
		{
			if (this.readyState == 4 && this.status == 200) 
			{
				document.getElementById("colorAddResult").innerHTML = "Contact has been added";
			}
		};
		xhr.send(jsonPayload);
	}
	catch(err)
	{
		document.getElementById("colorAddResult").innerHTML = err.message;
	}
	
}

function searchColor()
{
	let srch = document.getElementById("searchText").value;
	document.getElementById("colorSearchResult").innerHTML = "";
	
	let colorList = "";

	let tmp = {search:srch,userId:userId};
	let jsonPayload = JSON.stringify( tmp );

	let url = urlBase + '/SearchColors.' + extension;
	
	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
		xhr.onreadystatechange = function() 
		{
			if (this.readyState == 4 && this.status == 200) 
			{
				document.getElementById("colorSearchResult").innerHTML = "Color(s) has been retrieved";
				let jsonObject = JSON.parse( xhr.responseText );
				
				for( let i=0; i<jsonObject.results.length; i++ )
				{
					colorList += jsonObject.results[i];
					if( i < jsonObject.results.length - 1 )
					{
						colorList += "<br />\r\n";
					}
				}
				
				document.getElementsByTagName("p")[0].innerHTML = colorList;
			}
		};
		xhr.send(jsonPayload);
	}
	catch(err)
	{
		document.getElementById("colorSearchResult").innerHTML = err.message;
	}
	
}
function goToRegister()
{
	window.location.href = "register.html";
}
function backToLogin()
{
	window.location.href = "index.html";
}
function doRegister()
{
	let firstName = document.getElementById("enterFName").value;
	let	lastName = document.getElementById("enterLName").value;
	let login = document.getElementById("registerUsername").value;
	let password = document.getElementById("registerPassword").value;
	let tmp = {login, password, firstName, lastName};
	let jsonPayload = JSON.stringify( tmp );
	let url = urlBase + '/Register.' + extension;
	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
		xhr.onreadystatechange = function() 
		{
			if (this.readyState == 4 && this.status == 200) 
			{
				let jsonResponse = JSON.parse( xhr.responseText) 
				if (!jsonResponse.error)
				{
					document.getElementById("registerResult").innerHTML = "Register Successful";
				}
				else
				{
					document.getElementById("registerResult").innerHTML =	jsonResponse.error;
				}
			}
		};
		xhr.send(jsonPayload);
		
	}
	catch(err)
	{
		document.getElementById("registerResult").innerHTML = err.message;
	}
}

function doReview()//to do
{

}

function createContract()//to do
{

}



function searchButtonClick()
{
	searchText = document.getElementById("SearchBar").value;
	pageNum = 0;
	returnCount = 0; // Update page if no values are retuned.
	searchContacts(0, () => {
		console.log("Return (SP)" + returnCount);
	});

}

// function prevPage() 
// {
// 	// Do nothing if page number is zero.
// 	if (pageNum == 0)
// 	{
// 		// TODO Disable class
// 		return;
// 	}
// 	returnCount = 1; // Do not update page if no values are returned.
// 	searchContacts(pageNum - 1, () => {
// 		if (returnCount != 0)
// 		{
// 			pageNum -= 1;
// 		}
// 	});
// 	// If search returns values, update page number
	

// }

// function nextPage() 
// {
// 	// TODO. do not update if disabled (class)
// 	returnCount = 1; // Do not update page if no values are returned.
// 	console.log("Update " + pageNum);
// 	searchContacts(pageNum + 1, ()=> {
// 		console.log("Return (NP)" + returnCount);
// 		// If search returns values, update page number
// 		if (returnCount != 0)
// 		{
// 		pageNum += 1;
// 		}
// 		// TODO else disable.
// 	});
	
// }

// function searchContacts(PageN = 0, _callback)
// {
// 	document.getElementById("contactSearchResult").innerHTML = "";

// 	let tmp = {search: searchText,  userID: userId, page:PageN };
// 	let jsonPayload = JSON.stringify( tmp );

// 	let url = urlBase + '/SearchContacts.' + extension;
	
// 	let xhr = new XMLHttpRequest();
// 	xhr.open("POST", url, true);
// 	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
// 	try
// 	{
// 		xhr.onreadystatechange = function() 
// 		{
// 			if (this.readyState == 4 && this.status == 200) 
// 			{
// 				let jsonObject = JSON.parse( xhr.responseText );
// 				let jsonData = jsonObject.results;
// 				// Do not update if no values are returned and Return Count = 1;
// 				if ((returnCount == 1) && (jsonData.length == 0)) 
// 				{
// 					returnCount = 0;
// 					console.log("Return Count" + returnCount);
// 					// Run Callback
// 					_callback();
// 					return;
// 				}
// 				returnCount = jsonData.length;
// 				console.log("Return Count" + returnCount);
// 				let container = document.getElementById("contactTable");
// 				while (container.firstChild)
// 				{
// 					container.removeChild(container.firstChild);
// 				}
// 				// Create the table element
// 				let table = document.createElement("table");
// 				// Create the header element
// 				let thead = document.createElement("thead");
// 				let tr = document.createElement("tr");
				
// 				// Loop through the column names and create header cells
// 				let th = document.createElement("th");
// 				th.innerText = "First Name";
// 				tr.appendChild(th);
// 				th = document.createElement("th");
// 				th.innerText = "Last Name";
// 				tr.appendChild(th);
// 				th = document.createElement("th");
// 				th.innerText = "Phone Number";
// 				tr.appendChild(th);
// 				th = document.createElement("th");
// 				th.innerText = "Email";
// 				tr.appendChild(th);
// 				// jsonData.forEach(o => delete o.id)
// 				thead.appendChild(tr); // Append the header row to the header
// 				table.append(tr) // Append the header to the table
// 				// Loop through the JSON data and create table rows
// 				// TODO. use for loop to do this ten times
// 				for (let i = 0; i < 10; i++)
// 				{
// 					let tr = document.createElement("tr");
// 					if (i < jsonData.length)
// 					{
// 						tr.onclick = function () {highlightOnly(this);};
// 						// Get the values of the current object in the JSON data
// 				   		let vals = Object.values(jsonData[i]);
// 				  		// Loop through the values and create table cells
// 				  		vals.forEach((elem, index) => {
// 					  		//Skip First index
// 					  		if (index == 0)
// 					  		{
// 								tr.dataset.ID = elem;
// 					  		} else
// 					  		{
// 					  			let td = document.createElement("td");
// 					 			td.innerText = elem; // Set the value as the text of the table cell
// 								tr.appendChild(td); // Append the table cell to the table row
// 					  		}
// 				   		});
// 					}
// 					else 
// 					{
// 						for (let j = 0; j < 4; j++)
// 						{
// 							let td = document.createElement("td");
// 							td.innerText = ' ';
// 							console.log(td);
// 							tr.appendChild(td); // Append the table cell to the table row
// 						}
// 					}
// 					table.appendChild(tr); // Append the table row to the table
// 				}
// 				container.appendChild(table) // Append the table to the container element
// 				clearSelected();
// 				// Run Callback
// 				_callback();
// 			}
// 		};
// 		xhr.send(jsonPayload);
// 	}
// 	catch(err)
// 	{
// 		document.getElementById("contactSearchResult").innerHTML = err.message;
// 	}
// }



// // highlight and get contact ID.
// function highlightOnly(Crow)
// {
// 	// If row is already highlighted, remove highlight
// 	if (Crow.classList.contains("highlight")) {
// 		Crow.classList.remove("highlight");
		
// 		clearSelected();
// 		console.log("Console ID Cleared");
// 		return;
// 	}
// 	// Clear Table
// 	let tableR = Array.from(Crow.parentElement.children);
// 	tableR.forEach( (elm, index) => {
// 		if (index != 0) {
// 			elm.classList.remove("highlight");
// 		}
// 	});
// 	Crow.classList.add("highlight");
// 	contactID = Crow.dataset.ID;
// 	console.log("Console ID:" + contactID);
// 	// Enable Delete
// 	document.getElementById("delContactButton").disabled = false;
// 	setUpUpdate(Crow);
// }
// function clearSelected()
// {
// 	contactID = -1;
// 	// Disable Delete
// 	document.getElementById("delContactButton").disabled = true;
// 	setUpAdd();
// 	return;

// }

function addContract()
{
	document.getElementById("contactAddResult").innerHTML = "";
	let newFirstName = document.getElementById("firstNameText").value;
	let newLastName = document.getElementById("lastNameText").value;
	let newPhoneNumber = document.getElementById("phoneNumber").value;
	let newEmail = document.getElementById("emailText").value;
	let tmp = {firstName:newFirstName, lastName:newLastName, phone:newPhoneNumber, email: newEmail, userID: userId};
	let jsonPayload = JSON.stringify( tmp );
	console.log(jsonPayload);
	let url = urlBase + '/AddContact.' + extension;
	
	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
		xhr.onreadystatechange = function() 
		{
			if (this.readyState == 4 && this.status == 200) 
			{
				document.getElementById("contactAddResult").innerHTML = "Contact has been added";
				searchContacts(pageNum);
			}
		};
		xhr.send(jsonPayload);
	}
	catch(err)
	{
		document.getElementById("contactAddResult").innerHTML = err.message;
	}
	
}
function updContract()
{
	document.getElementById("contactAddResult").innerHTML = "";
	let cID = contactID;
	if (cID == -1){
		console.log("Error: No contact Selected");
		return;
	}
	let FirstName = document.getElementById("firstNameText").value;
	let LastName = document.getElementById("lastNameText").value;
	let PhoneNumber = document.getElementById("phoneNumber").value;
	let Email = document.getElementById("emailText").value;
	let tmp = {firstName:FirstName, lastName:LastName, phone:PhoneNumber, email: Email, contactID: cID};
	let jsonPayload = JSON.stringify( tmp );
	console.log(jsonPayload);
	let url = urlBase + '/UpdateContact.' + extension;
	
	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
		xhr.onreadystatechange = function() 
		{
			if (this.readyState == 4 && this.status == 200) 
			{
				document.getElementById("contactAddResult").innerHTML = "Contact has been Updated";
				searchContacts(pageNum);
			}
		};
		xhr.send(jsonPayload);
	}
	catch(err)
	{
		document.getElementById("contactAddResult").innerHTML = err.message;
	}
}
// Set Up Update
function setUpUpdate(row)
{
	if (contactID == -1) {
		console.log("Update Setup Error");
		return;
	}
	document.getElementById("contactAddResult").innerHTML = "";
	document.getElementById("addUpdateButton").innerHTML = "Update Contact";
	document.getElementById("editorLabel").innerHTML = "Contact to Update";
	document.getElementById("firstNameText").value = row.cells[0].innerText;
	document.getElementById("lastNameText").value = row.cells[1].innerText;
	document.getElementById("phoneNumber").value = row.cells[2].innerText;
	document.getElementById("emailText").value = row.cells[3].innerText;
}

// Set Up Add
function setUpAdd()
{
	document.getElementById("contactAddResult").innerHTML = "";
	document.getElementById("addUpdateButton").innerHTML = "Add Contact";
	document.getElementById("editorLabel").innerHTML = "Contact to Add";
	document.getElementById("firstNameText").value = "";
	document.getElementById("lastNameText").value = "";
	document.getElementById("phoneNumber").value = "";
	document.getElementById("emailText").value = "";
}

function addUpdateButton()
{
	if (contactID == -1) {
		addContact();
	}
	else {
		updContact();
	}
	
}
function delContract() {
	let cID = contactID;
	if (cID <= -1)
	{
		console.log("Nothing to delete");
		return
	}
	let tmp = {contactID:cID};
	let jsonPayload = JSON.stringify( tmp );
	let url = urlBase + '/DeleteContact.' + extension;
	let xhr = new XMLHttpRequest();

	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
		xhr.onreadystatechange = function() 
		{
			if (this.readyState == 4 && this.status == 200) 
			{
				document.getElementById("contactAddResult").innerHTML = "Contact has been Deleted";
				searchContacts(pageNum);
			}
		};
		xhr.send(jsonPayload);
	}
	catch(err)
	{
		document.getElementById("contactAddResult").innerHTML = err.message;
	}
}
function backToWhere()
{
	userId = -1;
	let data = document.cookie;
	let splits = data.split(",");
	for(var i = 0; i < splits.length; i++) 
	{
		let thisOne = splits[i].trim();
		let tokens = thisOne.split("=");
		if( tokens[0] == "firstName" )
		{
			firstName = tokens[1];
		}
		else if( tokens[0] == "lastName" )
		{
			lastName = tokens[1];
		}
		else if( tokens[0] == "userId" )
		{
			userId = parseInt( tokens[1].trim() );
		}
	}
	
	if( userId < 0 )
	{
		window.location.href = "index.html";
	}
	else
	{
		window.location.href = "contacts.html";
	}
}

function toAboutUs()
{
	window.location.href = "about.html";
}

function onEnter(key)
{
	if (key.keyCode == 13)
	{
		searchContacts();
	}
}