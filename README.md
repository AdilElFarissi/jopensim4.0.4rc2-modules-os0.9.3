## jOpenSim v4.0.4RC2 search and profile modules for OpenSimulator v0.9.3.
### Change Log
+ Fix to make GenericXMLRPCRequest use HttpClient set in WebUtils and configured on the [Startup] and [Network] sections of OpenSim.ini
+ Replaced UserProfileData (obsolete) by OpenSim core class UserProfileProperties.
+ Many magic touches :)

### Usage
Copy the content of the bin folder to the OpenSim bin folder and set following the instructions in your Joomla admin > Components > jOpenSim > Addon Help (Search and Profiles).

### Compile
Compile OpenSim first...
Place the "jOpenSim.Modules" folder into the OpenSim source root folder...
+ In Windows: run or double click
<pre>opensim-0.9.3-source\jOpenSim.Modules\runprebuild.bat
opensim-0.9.3-source\jOpenSim.Modules\compile.bat</pre>
+ In Linux&Co:
<pre>cd opensim-0.9.3-source/jOpenSim.Modules
./runprebuild.sh
./compile.sh</pre>
You will find the compiled DLLs in 
<pre>Win : opensim-0.9.3-source\jOpenSim.Modules\bin\net8.0\
Lin : opensim-0.9.3-source/jOpenSim.Modules/bin/net8.0/</pre>

### Thanks Where Due
+ This is a modified version of the jOpenSim Modules by [@FoTo50](https://github.com/FoTo50).
+ Special thanks to FoTo 50 and [jOpenSim Project](https://www.jopensim.com/).
+ Special thanks to the [OpenSimulator Project team](http://opensimulator.org/).
