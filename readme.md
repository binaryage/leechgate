# Google Analytics for your S3 bucket

## Problem: 

> I want to see my S3 bucket stats in Google Analytics

## Solution: 

> build minimalistic Heroku app which acts as a redirecting proxy which hits Google Analytics server with every visit

### My motivation

I host my download server for my Mac apps on Amazon S3. Also I host there Sparkle Updater's XML files. I want analytics!

### How it works

When someone requests http://downloads.binaryage.com/TotalTerminal-1.2.3.dmg, it goes to Heroku app, which:
	
  1. parses URL into domain, product and version (download.binaryage.com, TotalTerminal, 1.2.3)
  2. hits Google Analytics with event (category=domain, action=product, label=version)
  3. redirects to http://downloads-1.binaryage.com/TotalTerminal-1.2.3.dmg (HTTP 307)
  
### In Action

		➔ curl -I http://downloads.binaryage.com/TotalTerminal-1.2.3.dmg
		
        HTTP/1.1 307 Temporary Redirect
		Content-Type: text/html
		Date: Fri, 09 Mar 2012 10:20:59 GMT
		Location: http://downloads-1.binaryage.com/TotalTerminal-1.2.3.dmg
		Server: Apache/2.2.19 (Unix) PHP/5.3.6
		X-Powered-By: PHP/5.3.6
		Connection: keep-alive  

### Installation
  
  * DNS, S3
    * originally:
      * had S3 bucket: `downloads.binaryage.com`
	  * had DNS mapping: `downloads.binaryage.com` (DNS) -> `downloads.binaryage.com.s3.amazonaws.com` (S3 bucket)

    * newly:
      * created new Heroku app: `binaryage-leechgate` with content of this repo
      * created new S3 bucket: `downloads-1.binaryage.com`
	  * copied S3 bucket content: from `downloads.binaryage.com` to `downloads-1.binaryage.com`
	  * set new DNS mapping: `downloads-1.binaryage.com` (DNS) -> `downloads-1.binaryage.com.s3.amazonaws.com` (S3 bucket)
	  * modified original DNS mapping: `downloads.binaryage.com` (DNS) -> `binaryage-leechgate.herokuapp.com` (Heroku)

  * Heroku
	* fork & clone
	* edit _config.php
	* `git commit`

	* `heroku create --stack cedar`
    * `heroku config:add LEECHGATE_GAID=YOUR_GOOGLE_ANALYTICS_ID`
    * `git push heroku master` 
		
    * [http://devcenter.heroku.com/articles/config-vars]()
	* [http://devcenter.heroku.com/articles/custom-domains]()