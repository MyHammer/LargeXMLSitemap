# Name
LargeXMLSitemap
PHP classes for generation of huge XML sitemaps

# About
LargeXMLSitemap allows PHP developers to generate search engine XML sitemaps
with millions of URLs, and automatically handles correct sizing, splitting and
compression of the generated XML file structure. Unlike other tools,
LargeXMLSitemap does not crawl your site to generate a XML structure of your
browseable URLs - it's a collection of PHP classes which can be used from
within your sites code to generate these structures. If, for example, you have
an online shopping site, it's very easy to create correctly structured, sized
and compressed XML sitemaps with the URLs to all your product pages.
Customized versions of these classes are in production use on the MyHammer
platform and are reliably generating sitemaps with millions of URLs each day.

# Aim of this project
We are releasing those classes as Open Source Software because we believe
they might be useful for other sites which need to generate huge XML sitemap
structures, and because there is still room for a lot of optimizations and more
features. There is a Google Group for all discussions related to our
Open Source projects - we would love to hear your opinion, ideas and questions:
http://groups.google.com/group/myhammer-opensource

# Usage
Please see the examples.

## General
cXmlSitemapConfig is optional and not used inside the other classes.
Provide filename without ending. .xml or xml.gz are appended depending on compress flag.
The sitemap filenames get a counter starting with 0.

## Create new Sitemap files
Create a new Generator Object
	$oSitemapCreator = new cXmlSitemapGeneratorWrite(FILENAME, SAVE COMPRESSED, PATH);

Delete the current files
	$oSitemapCreator->deleteCurrent();

Open the first file
	$oSitemapCreator->open();

Add some URLs to the Sitemap
	$oSitemapCreator->addUrl(URL, Last modification time, change frequency, priority)
	$oSitemapCreator->addUrl(URL, Last modification time, change frequency, priority)
	$oSitemapCreator->addUrl(URL, Last modification time, change frequency, priority)

Close the generator and save the last sitemap file
	$oSitemapCreator->save();

Update the sitemap index
	$oSitemapCreator->updateSitemapIndex(Path to Sitemap Indexfile);

# Authors
Jan Christiansen

# Contact
Send suggestions, Feedback, problems or whatever to opensource@myhammer.de
