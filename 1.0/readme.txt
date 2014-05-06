=== WebEmailProtector ===
Contributors: dsrodzin
Donate link: http://www.webemailprotector.com/
Tags: mail, email, EMail, e-mail, E-mail, email hider, address, emo, obfuscate, obfuscation, protect, protection, harvesting, harvester, spam, protection, anti-spam, antispam, block, crawler, encode, encoder, encoding, encrypt, encryption,  encrypter, robots, spam, spambot, spider, virus, anti virus, anti-virus, identity theft, id theft
Requires at least: 3.0.1
Tested up to: 3.9
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

What is it?

WebEmailProtector is a professional grade EMO (Email Obfuscator). It allows you to securely present 
your email contacts on your web-site, for people to be able to easily contact you through your favourite email tool, 
but prevents it from being harvested.

We provide you with an EMO API key that is accessed and setup through the Plugin. This means that your email address is 
no longer stored and presented from your site.
 
The most common way commercial harvesters operate is for them to use software "scrapers" to steal the address text directly 
from your web site. We detect and block all such actions, and hence make your email visible to bona-fide people but not to 
machines or spammers. The fact that you use our EMO is completely transparent to genuine users, its less cumbersome than a 
contact form or captcha, and more reliable than a 'free' or otherwise eoncoder, and can be easily added to any web page. 

What does it do?

The EMO API detects who and what is trying to use an email link on your site, and then uses various mechanisms to determine if 
this request is being made by a bona-fide user. Once a user tries to accesses your email your web site automatically contacts 
our server on which a validation process is run, and we only return your secured email address if certain criteria are met. 
It all happens in the blink of an eye and does not slow down your service. And just in case you wonder – we do not see or track 
the email itself, we only authorise the release of your address so your privacy is protected.

How do you use it?

After installing, fFollow the instructions on th settings menu WordPress page.

== Installation ==


1. Install the webemailprotector plugin (places `plugin-name.php` and support files in the `/wp-content/plugins/` directory)
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Follow the instructions on the settings page.

== Frequently Asked Questions ==

= What is the EMO service? =
Email address harvesters operate by using software "scrapers" or "click through" staff to steal email addresses directly from web-site pages.
Using our EMO (Email Obfuscator) service prevents this as your email addresses no longer need to be listed directly on your web-site. Instead 
they are hidden behind a security firewall on our server. We then only release the address after 
we are sure it is not being accessed improperly. Because of this it becomes invisible to harvesters and machines and yet is completely 
visible to bona fide users.  

= How does an EMO protected email address appear? =
he EMO protected email address appears on a site like this: "<a href='JavaScript:emo(<yourAPIkey>);'>Any Text</a>"
(you can change what it says and how it looks)
whereas a non protected email address usually appears like this:"<a href='mailto:nobody@webemailprotector.com'>nobody@webemailprotector.com</a>". 
Both operate when you "click", but only the EMO one is safe.

= Why is it better than a Contact Form? =
Contact Forms are effective in dealing with both human and machine based harvesting, unless you ever want to use email auto responders of course 
as this gives your identity away.
However the real problem is that all the form filling can annoy and therefore disuade real users from getting in touch - effectively putting a 
barrier between you and your users in your most important communications channel - email

= Why is it better than Captcha codes? =
Captcha codes are effective in dealing with machines but not with users who simply click to reveal your address.
And the other main issue is that they are very very annoying to real users and can heavily disuade communications with you - so are effectively 
putting a barrier between you and your wanted users.

= Why is it better than 'free' address encoder? =
Although at first they may appear effective as your 'mailto:' text is no longer obvious to the eye within your HTML code. Encoders and encrypters, 
'free' or otherwise, have to be built so that they can be interpreted by any web-browser using standard HTML. And this is the nature of there 
drawback. 
They are more or less complex but involve a Java/JavaScript sequence munger or character set coder. But to cut a long story short if your browser 
understands them so can any harvesting software. 
So it's actually quite simple for encoded email addresses to be interpretted and harvested using standard software libraries.

= Where do you place the EMO API code? =
Using the plugin a few lines of code are placed in the <head> section of your HTML file.
To use replace any <a> anchor link email references in the HTML file with the EMO API code.
Again, we send you the code and instructions after sign-up. But it's as simple as that to get protected.

= Do you have to be a web expert to install the EMO API code? ==
No, it's designed to be easy to install, and we send you full instructions and offer full support incase of any difficulties.

= How do you get help with installing the EMO API code? ==
Contact us at Support using the <a href="./contact.html#phone">Phone Number</a> or <a href="./contact.html#email">Email Address</a> listed.

= How is the email protected? ==
We provide protection as the email address is never listed or disclosed on your website. Instead the email address is held in a secure
place on our server and only loaded up when we verify the request is genuine. 

= What do you get for the FREE trial? =
You get the full service free for 6 months, with exactly the same protection as offered with a paid up subscription. 
There is no obligation to purchase, but at the end of the trial we are confident that you will see a reduction in volume of new email 
spam originating from your web site. At the end of the trial we would also ask you to complete a 10 question survey on how you found 
installing and using it.
Please note that as its a trial you only get it once per email/website for you to try unless otherwise agreed with us.

= How do you subscribe following the trial? =
Once your trial is coming to an end we will send you an email detailing what you have to do to continue the services. All
payments can be made on-line using PayPal. the more people that use it the more we can keep prices down.

= How can you check if you are protected? =
Once installed, click on your Email and your installed email tool should open as normal. If you really want to check your email is no longer 
listed view your web site in text mode (e.g. pressing the F12 key in your Internet Explorer/Google Chrome/Firefox web browser) and search for 
your email address or the mailto: reference. It should no longer be there.

= Do you protect our data privacy (including the email)? =
Yes we most certainly do and take data protection very seriously. 
We do not use your information for any marketing purposes and we do not divuldge it to 3rd parties. Further details can be found at our
<a href="privacy.html">privacy policy</a> page.<br>
And just in case you wonder – we do not see or track the email itself, we only authorise the release of your address so your communication privacy is protected.</p>

= Do you provide 24/7 availability ie up-time? =
The EMO software runs on a Commercial Grade Server. But like any computer system it is possible that 
the server will fail from time to time, these occurances are less than a few moments a month. It runs on a solid, secure network infrastructure 
comprising a pooled server environment, which gives virtually any server on its network the ability 
to service Websites when a request occurs. With an aggregated SLA of >99.9% guaranteed up-time and 24/7 monitoring.<br><br>
Please see more information at our <a href="terms.html#guarantee"> terms and conditions</a> page. 

= Can you change the name displayed on screen for the email? =
Yes, just replace >Your Name< in the anchor element to anything you like eg >John Doe< to >Email Me<.

= Can you change the html styling? =
Yes, just style it using CSS, style sheets or in-line, as you would with any <a> anchor element.

== Screenshots ==

1. This is how the email address looks like on a web page '/assets/screenshot-1.png`

2. This is what the same html code looks like '/assets/screenshot-2.png' (the real code has been replaced with ####-####-####-####)


== Changelog ==

= 1.0 =
First release onto private Wordpress sites 6th May 2014

== Upgrade Notice ==

None