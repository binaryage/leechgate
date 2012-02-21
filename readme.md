# Google Analytics for S3 bucket

## Problem: I want to see my S3 bucket stats in Google Analytics
## Solution: minimalistic Heroku app which acts as a redirecting proxy

### Motivation

On S3 I host a download server for my Mac apps: TotalFinder, TotalTerminal, etc. Also I host there sparkle appcast update XML files. I want analytics!

### How it works

When someone requests http://downloads.binaryage.com/TotalTerminal-1.2.3.dmg, it goes to Heroku app, which:
	
  1. parses URL into domain, product and version (download.binaryage.com, TotalTerminal, 1.2.3)
  2. hits Google Analytics with event (category=domain, action=product, label=version)
  3. redirects to http://downloads-1.binaryage.com/TotalTerminal-1.2.3.dmg (HTTP 307)

### Installation
  
  1. DNS, S3

		originally:
			downloads.binaryage.com (DNS) -> downloads.binaryage.com.s3.amazonaws.com (S3 bucket)

		newly:
			renamed S3 bucket to downloads-1.binaryage.com
			downloads-1.binaryage.com (DNS) -> downloads-1.binaryage.com.s3.amazonaws.com (S3 bucket)
			downloads.binaryage.com (DNS) -> binaryage-leechgate.herokuapp.com (Heroku)

  2. Heroku

		fork & clone
		edit _config.php
		git commit

		heroku create --stack cedar
		git push heroku master 
		
		http://devcenter.heroku.com/articles/custom-domains