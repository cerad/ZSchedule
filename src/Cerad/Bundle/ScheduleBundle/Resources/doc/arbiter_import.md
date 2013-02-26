It's very unfortunate that arbiter has a rather bizzare notion of what exported report should look like.
As of this writing, there is no single report which covers all information.

In fact, as far as I can tell, there is not report at all which indicates if the teams are of different levels.

The best report is the slot report located under the report menu.  This gives you basic information.  
The xml version is easy to parse and can be read one record at a time.  
I have not yet tried importing the csv version though it looks okay.

The slot report does NOT include:
1. Individual team levels
2. Referee status (accepted etc) and emails, do get the slot name and referee name.

For now I pretty much just ignore item 1.  
It's possible that if each team has a unique name then one could look up the team level.
However, high school uses the same name for all levels.

The only report I have found which gives you the referee status and email is the Portrait game report available from the assigning screen.
Parsing this one is a true adventure as each game is broken up into multiple lines.
It's pretty much impossible to import this as a stand alone because sport & level, date/time, field, teams etc are broken
across multiple lines and there is no way that I can tell to put them back together.

So I rely on the having the slot report imported first then the portrait report.  Not ideal but works in practice.

The portrait report gets real big.  Currently I have to load the entire file into a big array which takes quite a bit of time and memory.

There is no option for csv though I suppose you could pull up the file in Excel then do a save as.  Something I would like to avoid.

Need to research further into reading one row at a time from a spreadsheet using PHPExcel.
Streaming is pretty much not available through PHPExcel.

There is a text file format which resembles a csv file.  
However, the commas are not escaped so it might be fragile.
If the comma issue can be overcome then it should be super fast to process.

