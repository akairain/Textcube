<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'GET' => array(
		'includeFileContents' => array(array('true', 'false'))
	)
);
require ROOT . '/library/includeForBlogOwner.php';
set_time_limit(0);
$includeFileContents = Validator::getBool(@$_GET['includeFileContents']);
$writer = new OutputWriter();
if (defined('__TEXTCUBE_BACKUP__')) {
	if (!file_exists(ROOT . '/cache/backup')) {
		mkdir(ROOT . '/cache/backup');
		@chmod(ROOT . '/cache/backup', 0777);
	}
	if (!is_dir(ROOT . '/cache/backup')) {
		exit;
	}
	if ($writer->openFile(ROOT . "/cache/backup/$blogid.xml")) {
	} else {
		exit;
	}
} else {
	if ($writer->openStdout()) {
		header('Content-Disposition: attachment; filename="Textcube-Backup-' . Timestamp::getDate() . '.xml"');
		header('Content-Description: Textcube Backup Data');
		header('Content-Transfer-Encoding: binary');
		header('Content-Type: application/xml');
	} else {
		exit;
	}
}
$newlineStyle = (!is_null(getServiceSetting('newlineStyle')) ? ' format="'.getServiceSetting('newlineStyle').'"' : '');
$writer->write('<?xml version="1.0" encoding="utf-8" ?>');
$writer->write('<blog type="tattertools/1.1" migrational="false">');
$setting = new BlogSetting();
if ($setting->load()) {
	$setting->escape();
	$writer->write('<setting>' . '<name>' . $setting->name . '</name>' . '<secondaryDomain>' . $setting->secondaryDomain . '</secondaryDomain>' . '<defaultDomain>' . Validator::getBit($setting->defaultDomain) . '</defaultDomain>' . '<title>' . $setting->title . '</title>' . '<description>' . UTF8::correct($setting->description) . '</description>' . '<banner><name>' . $setting->banner . '</name>');
	if ($includeFileContents && file_exists(ROOT . "/attach/$blogid/{$setting->banner}")) {
		$writer->write('<content>');
		if (!empty($setting->banner) && file_exists(ROOT . "/attach/$blogid/" . $setting->banner))
			Base64Stream::encode(ROOT . "/attach/$blogid/{$setting->banner}", $writer);
		$writer->write('</content>');
	}
	$writer->write('</banner>' . '<useSloganOnPost>' . Validator::getBit($setting->useSloganOnPost) . '</useSloganOnPost>' . '<postsOnPage>' . $setting->postsOnPage . '</postsOnPage>' . '<postsOnList>' . $setting->postsOnList . '</postsOnList>' . '<postsOnFeed>' . $setting->postsOnFeed . '</postsOnFeed>' . '<publishWholeOnFeed>' . Validator::getBit($setting->publishWholeOnFeed) . '</publishWholeOnFeed>' . '<acceptGuestComment>' . Validator::getBit($setting->acceptGuestComment) . '</acceptGuestComment>' . '<acceptCommentOnGuestComment>' . Validator::getBit($setting->acceptCommentOnGuestComment) . '</acceptCommentOnGuestComment>' . '<language>' . $setting->language . '</language>' . '<timezone>' . $setting->timezone . '</timezone>' . '</setting>');
	$writer->write(CRLF);
}
$category = new Category();
if ($category->open()) {
	do {
		if($category->id != 0) {
		   	$category->escape();
			$writer->write('<category>' . '<name>' . $category->name . '</name>' . '<priority>' . $category->priority . '</priority>');
			if ($childCategory = $category->getChildren()) {
				do {
					$childCategory->escape();
					$writer->write('<category>' . '<name>' . $childCategory->name . '</name>' . '<priority>' . $childCategory->priority . '</priority>' . '</category>');
				} while ($childCategory->shift());
				$childCategory->close();
			}
			$writer->write('</category>');
			$writer->write(CRLF);
		} else {
		   	$category->escape();
			$writer->write('<category>' . '<name>' . $category->name . '</name>' . '<priority>' . $category->priority . '</priority>');
			$writer->write('<root>1</root>');
			$writer->write('</category>');
			$writer->write(CRLF);
		}
	} while ($category->shift());
	$category->close();
}
$post = new Post();
if ($post->open('', '*', 'published, id')) {
	do {
		$writer->write('<post' . ' slogan="' . htmlspecialchars($post->slogan) . '"' . $newlineStyle . '>' . 
			'<id>' . $post->id . '</id>' . 
			'<visibility>' . $post->visibility . '</visibility>' . 
			'<starred>' . $post->starred . '</starred>' . 
			'<title>' . htmlspecialchars($post->title) . '</title>' . 
			'<content formatter="' . htmlspecialchars($post->contentFormatter) . '" editor="' . htmlspecialchars($post->contentEditor) .'">' . htmlspecialchars(UTF8::correct($post->content)) . '</content>' . 
			'<location>' . htmlspecialchars($post->location) . '</location>' . 
			(!is_null($post->password) ? '<password>' . htmlspecialchars($post->password) . '</password>' : '') . 
			'<acceptComment>' . $post->acceptComment . '</acceptComment>' . 
			'<acceptTrackback>' . $post->acceptTrackback . '</acceptTrackback>' . 
			'<published>' . $post->published . '</published>' . 
			'<created>' . $post->created . '</created>' . 
			'<modified>' . $post->modified . '</modified>');

		if ($post->category)
			$writer->write('<category>' . htmlspecialchars(Category::getLabel($post->category)) . '</category>');
		if ($post->loadTags()) {
			foreach ($post->tags as $tag)
				$writer->write('<tag>' . htmlspecialchars($tag) . '</tag>');
		}
		$writer->write(CRLF);
		if ($attachment = $post->getAttachments()) {
			do {
				$writer->write('<attachment' . ' mime="' . htmlspecialchars($attachment->mime) . '"' . ' size="' . $attachment->size . '"' . ' width="' . $attachment->width . '"' . ' height="' . $attachment->height . '"' . '>' . '<name>' . htmlspecialchars($attachment->name) . '</name>' . '<label>' . htmlspecialchars($attachment->label) . '</label>' . '<enclosure>' . ($attachment->enclosure ? 1 : 0) . '</enclosure>' . '<attached>' . $attachment->attached . '</attached>' . '<downloads>' . $attachment->downloads . '</downloads>');
				if ($includeFileContents && file_exists(ROOT . "/attach/$blogid/{$attachment->name}")) {
					$writer->write('<content>');
					Base64Stream::encode(ROOT . "/attach/$blogid/{$attachment->name}", $writer);
					$writer->write('</content>');
				}
				$writer->write('</attachment>');
				$writer->write(CRLF);
			} while ($attachment->shift());
			$attachment->close();
		}
		if ($comment = $post->getComments()) {
			do {
				if($comment->isFiltered == 0) {
					$writer->write('<comment>' . '<id>'. $comment->id . '</id>' . '<commenter' . ' id="' . $comment->commenter . '">' . '<name>' . htmlspecialchars(UTF8::correct($comment->name)) . '</name>' . '<homepage>' . htmlspecialchars(UTF8::correct($comment->homepage)) . '</homepage>' . '<ip>' . $comment->ip . '</ip>' . '<openid>' . $comment->openid . '</openid>' . '</commenter>' . '<content>' . htmlspecialchars($comment->content) . '</content>' . '<password>' . htmlspecialchars($comment->password) . '</password>' . '<secret>' . htmlspecialchars($comment->secret) . '</secret>' . '<written>' . $comment->written . '</written>' . '<isFiltered>' . $comment->isFiltered . '</isFiltered>');
					$writer->write(CRLF);
					if ($childComment = $comment->getChildren()) {
						do {
							if($childComment->isFiltered == 0) {
								$writer->write('<comment>' . '<id>' . $childComment->id . '</id>' . '<commenter' . ' id="' . $childComment->commenter . '"' . '>' . '<name>' . htmlspecialchars(UTF8::correct($childComment->name)) . '</name>' . '<homepage>' . htmlspecialchars(UTF8::correct($childComment->homepage)) . '</homepage>' . '<ip>' . $childComment->ip . '</ip>' . '<openid>' . $comment->openid . '</openid>' . '</commenter>' . '<content>' . htmlspecialchars($childComment->content) . '</content>' . '<password>' . htmlspecialchars($childComment->password) . '</password>' . '<secret>' . htmlspecialchars($childComment->secret) . '</secret>' . '<written>' . $childComment->written . '</written>' . '<isFiltered>' . $childComment->isFiltered . '</isFiltered>' . '</comment>');
							$writer->write(CRLF);
							}
						} while ($childComment->shift());
						$childComment->close();
					}
					$writer->write('</comment>');
					$writer->write(CRLF);
				}
			} while ($comment->shift());
			$comment->close();
		}
		if ($trackback = $post->getTrackbacks()) {
			do {
				if($trackback->isFiltered == 0) {
					$writer->write('<trackback>' . '<url>' . htmlspecialchars(UTF8::correct($trackback->url)) . '</url>' . '<site>' . htmlspecialchars(UTF8::correct($trackback->site)) . '</site>' . '<title>' . htmlspecialchars(UTF8::correct($trackback->title)) . '</title>' . '<excerpt>' . htmlspecialchars(UTF8::correct($trackback->excerpt)) . '</excerpt>' . '<ip>' . $trackback->ip . '</ip>' . '<received>' . $trackback->received . '</received>' . '<isFiltered>' . $trackback->isFiltered . '</isFiltered>' . '</trackback>');
					$writer->write(CRLF);
				}
			} while ($trackback->shift());
			$trackback->close();
		}
		if ($log = $post->getTrackbackLogs()) {
			$writer->write('<logs>');
			do {
				$writer->write('<trackback>' . '<url>' . htmlspecialchars(UTF8::correct($log->url)) . '</url>' . '<sent>' . $log->sent . '</sent>' . '</trackback>');
				$writer->write(CRLF);
			} while ($log->shift());
			$writer->write('</logs>');
			$log->close();
		}
		$writer->write('</post>');
		$writer->write(CRLF);
	} while ($post->shift());
	$post->close();
}
$notice = new Notice();
if ($notice->open()) {
	do {
		$writer->write('<notice' . ' slogan="' . htmlspecialchars($notice->slogan) . '"' . $newlineStyle . '>' . 
			'<id>' . $notice->id . '</id>' . 
			'<visibility>' . $notice->visibility . '</visibility>' . 
			'<starred>' . $notice->starred . '</starred>' . 
			'<title>' . htmlspecialchars(UTF8::correct($notice->title)) . '</title>' . 
			'<content formatter="' . htmlspecialchars($notice->contentFormatter) . '" editor="' . htmlspecialchars($notice->contentEditor) .'">' . htmlspecialchars(UTF8::correct($notice->content)) . '</content>' . 
			'<published>' . $notice->published . '</published>' . 
			'<created>' . $notice->created . '</created>' . 
			'<modified>' . $notice->modified . '</modified>');

		$writer->write(CRLF);
		if ($attachment = $notice->getAttachments()) {
			do {
				$writer->write('<attachment' . ' mime="' . htmlspecialchars($attachment->mime) . '"' . ' size="' . $attachment->size . '"' . ' width="' . $attachment->width . '"' . ' height="' . $attachment->height . '"' . '>' . '<name>' . htmlspecialchars($attachment->name) . '</name>' . '<label>' . htmlspecialchars($attachment->label) . '</label>' . '<enclosure>' . ($attachment->enclosure ? 1 : 0) . '</enclosure>' . '<attached>' . $attachment->attached . '</attached>' . '<downloads>' . $attachment->downloads . '</downloads>');
				if ($includeFileContents && file_exists(ROOT . "/attach/$blogid/{$attachment->name}")) {
					$writer->write('<content>');
					Base64Stream::encode(ROOT . "/attach/$blogid/{$attachment->name}", $writer);
					$writer->write('</content>');
				}
				$writer->write('</attachment>');
				$writer->write(CRLF);
			} while ($attachment->shift());
			$attachment->close();
		}
		$writer->write('</notice>');
		$writer->write(CRLF);
	} while ($notice->shift());
	$notice->close();
}
$keyword = new Keyword();
if ($keyword->open()) {
	do {
		$writer->write('<keyword' . $newlineStyle . '>' . 
			'<id>' . $keyword->id . '</id>' . 
			'<visibility>' . $keyword->visibility . '</visibility>' . 
			'<starred>' . $keyword->starred . '</starred>' . 			
			'<name>' . htmlspecialchars(UTF8::correct($keyword->name)) . '</name>' . 
			'<description editor="' . htmlspecialchars($keyword->descriptionEditor) . '" formatter="' . htmlspecialchars($keyword->descriptionFormatter) .'">' . htmlspecialchars(UTF8::correct($keyword->description)) . '</description>' .
			'<published>' . $keyword->published . '</published>' . 
			'<created>' . $keyword->created . '</created>' . 
			'<modified>' . $keyword->modified . '</modified>');

		$writer->write(CRLF);
		if ($attachment = $keyword->getAttachments()) {
			do {
				$writer->write('<attachment' . ' mime="' . htmlspecialchars($attachment->mime) . '"' . ' size="' . $attachment->size . '"' . ' width="' . $attachment->width . '"' . ' height="' . $attachment->height . '"' . '>' . '<name>' . htmlspecialchars($attachment->name) . '</name>' . '<label>' . htmlspecialchars($attachment->label) . '</label>' . '<enclosure>' . ($attachment->enclosure ? 1 : 0) . '</enclosure>' . '<attached>' . $attachment->attached . '</attached>' . '<downloads>' . $attachment->downloads . '</downloads>');
				if ($includeFileContents && file_exists(ROOT . "/attach/$blogid/{$attachment->name}")) {
					$writer->write('<content>');
					Base64Stream::encode(ROOT . "/attach/$blogid/{$attachment->name}", $writer);
					$writer->write('</content>');
				}
				$writer->write('</attachment>');
				$writer->write(CRLF);
			} while ($attachment->shift());
			$attachment->close();
		}
		$writer->write('</keyword>');
		$writer->write(CRLF);
	} while ($keyword->shift());
	$keyword->close();
}
$link = new Link();
if ($link->open()) {
	do {
		$writer->write('<link>' . '<url>' . htmlspecialchars(UTF8::correct($link->url)) . '</url>' . '<title>' . htmlspecialchars(UTF8::correct($link->title)) . '</title>' . '<feed>' . htmlspecialchars(UTF8::correct($link->feed)) . '</feed>' . '<registered>' . $link->registered . '</registered>' . '</link>');
		$writer->write(CRLF);
	} while ($link->shift());
	$link->close();
}
$log = new RefererLog();
if ($log->open()) {
	$writer->write('<logs>');
	$writer->write(CRLF);
	do {
		$writer->write('<referer>' . '<url>' . htmlspecialchars(UTF8::correct($log->url)) . '</url>' . '<referred>' . $log->referred . '</referred>' . '</referer>');
		$writer->write(CRLF);
	} while ($log->shift());
	$writer->write('</logs>');
	$writer->write(CRLF);
	$log->close();
}
$cmtNotified = new CommentNotified();
$cur_siteinfo = array();
$i = 0;
if ($cmtNotified->open()) {
	$writer->write('<commentsNotified>');
	$writer->write(CRLF);
	do {
		$writer->write('<comment>');
		$writer->write('<id>' . $cmtNotified->id . '</id>');
		$writer->write('<commenter>');
		$writer->write('<name>' . htmlspecialchars(UTF8::correct($cmtNotified->name)) . '</name>');
		$writer->write('<homepage>' . htmlspecialchars(UTF8::correct($cmtNotified->homepage)) . '</homepage>');
		$writer->write('<ip>' . $cmtNotified->ip . '</ip>');
		$writer->write('</commenter>');
		$writer->write('<entry>' . $cmtNotified->entry . '</entry>');
		$writer->write('<content>' . htmlspecialchars(UTF8::correct($cmtNotified->content)). '</content>');
		$writer->write('<password>' . htmlspecialchars($cmtNotified->password) . '</password>');
		$writer->write('<parent>' . htmlspecialchars($cmtNotified->parent) . '</parent>');
		$writer->write('<secret>' . $cmtNotified->secret . '</secret>');
		$writer->write('<written>' . $cmtNotified->written . '</written>');
		$writer->write('<modified>' . $cmtNotified->modified . '</modified>');
		$site = new CommentNotifiedSiteInfo();
		$site->open("id = {$cmtNotified->siteId}");
		$writer->write('<site>' . htmlspecialchars(UTF8::correct($site->url)) . '</site>');
		$cur_siteinfo[$i] = $site->id; $i++;
		$site->close();
		$writer->write('<remoteId>' . $cmtNotified->remoteId . '</remoteId>');
		$writer->write('<isNew>' . $cmtNotified->isNew . '</isNew>');
		$writer->write('<url>' . htmlspecialchars(UTF8::correct($cmtNotified->url)). '</url>');
		$writer->write('<entryTitle>' . htmlspecialchars(UTF8::correct($cmtNotified->entryTitle)). '</entryTitle>');
		$writer->write('<entryUrl>' . htmlspecialchars(UTF8::correct($cmtNotified->entryUrl)). '</entryUrl>');
		$writer->write('</comment>');		
		$writer->write(CRLF);
	} while ($cmtNotified->shift());
	$writer->write('</commentsNotified>');
	$writer->write(CRLF);
	$cmtNotified->close();
}
$cmtNotifiedSite = new CommentNotifiedSiteInfo();
if ($cmtNotifiedSite->open()) {
	$writer->write('<commentsNotifiedSiteInfo>');
	do {
		if (in_array($cmtNotifiedSite->id, $cur_siteinfo)) {
			$writer->write('<site>');
			$writer->write('<title>' . htmlspecialchars(UTF8::correct($cmtNotifiedSite->title)) . '</title>');
			$writer->write('<name>' . htmlspecialchars(UTF8::correct($cmtNotifiedSite->name)) . '</name>');
			$writer->write('<url>' . htmlspecialchars(UTF8::correct($cmtNotifiedSite->url)) . '</url>');
			$writer->write('<modified>' . $cmtNotifiedSite->modified . '</modified>');
			$writer->write('</site>');
		}
	} while ($cmtNotifiedSite->shift());
	$writer->write('</commentsNotifiedSiteInfo>');
	$writer->write(CRLF);
	$cmtNotifiedSite->close();
}
$statistics = new RefererStatistics();
if ($statistics->open()) {
	$writer->write('<statistics>');
	do {
		$writer->write('<referer>' . '<host>' . htmlspecialchars(UTF8::correct($statistics->host)) . '</host>' . '<count>' . $statistics->count . '</count>' . '</referer>');
		$writer->write(CRLF);
	} while ($statistics->shift());
	$writer->write('</statistics>');
	$writer->write(CRLF);
	$statistics->close();
}
$statistics = new BlogStatistics();
if ($statistics->load()) {
	$writer->write('<statistics>' . '<visits>' . $statistics->visits . '</visits>' . '</statistics>');
	$writer->write(CRLF);
}
$statistics = new DailyStatistics();
if ($statistics->open()) {
	$writer->write('<statistics>');
	do {
		$writer->write('<daily>' . '<date>' . $statistics->date . '</date>' . '<visits>' . $statistics->visits . '</visits>' . '</daily>');
		$writer->write(CRLF);
	} while ($statistics->shift());
	$writer->write('</statistics>');
	$writer->write(CRLF);
	$statistics->close();
}
$setting = new SkinSetting();
if ($setting->load()) {
	$writer->write('<skin>' . '<name>' . $setting->skin . '</name>' . '<entriesOnRecent>' . $setting->entriesOnRecent . '</entriesOnRecent>' . '<commentsOnRecent>' . $setting->commentsOnRecent . '</commentsOnRecent>' . '<trackbacksOnRecent>' . $setting->trackbacksOnRecent . '</trackbacksOnRecent>' . '<commentsOnGuestbook>' . $setting->commentsOnGuestbook . '</commentsOnGuestbook>' . '<tagsOnTagbox>' . $setting->tagsOnTagbox . '</tagsOnTagbox>' . '<alignOnTagbox>' . $setting->alignOnTagbox . '</alignOnTagbox>' . '<expandComment>' . $setting->expandComment . '</expandComment>' . '<expandTrackback>' . $setting->expandTrackback . '</expandTrackback>' . '<recentNoticeLength>' . $setting->recentNoticeLength . '</recentNoticeLength>' . '<recentEntryLength>' . $setting->recentEntryLength . '</recentEntryLength>' . '<recentTrackbackLength>' . $setting->recentTrackbackLength . '</recentTrackbackLength>' . '<linkLength>' . $setting->linkLength . '</linkLength>' . '<showListOnCategory>' . $setting->showListOnCategory . '</showListOnCategory>' . '<showListOnArchive>' . $setting->showListOnArchive . '</showListOnArchive>' . '<tree>' . '<name>' . $setting->tree . '</name>' . '<color>' . $setting->colorOnTree . '</color>' . '<bgColor>' . $setting->bgColorOnTree . '</bgColor>' . '<activeColor>' . $setting->activeColorOnTree . '</activeColor>' . '<activeBgColor>' . $setting->activeBgColorOnTree . '</activeBgColor>' . '<labelLength>' . $setting->labelLengthOnTree . '</labelLength>' . '<showValue>' . $setting->showValueOnTree . '</showValue>' . '</tree>' . '</skin>');
	$writer->write(CRLF);
}
$setting = new PluginSetting();
if ($setting->open()) {
	do {
		$writer->write('<plugin>' . '<name>' . $setting->name . '</name>' . '<setting>' . htmlspecialchars($setting->setting) . '</setting>' . '</plugin>');
		$writer->write(CRLF);
	} while ($setting->shift());
	$setting->close();
}
$setting = new UserSetting();
if ($setting->open()) {
	do {
		$writer->write('<userSetting>' . '<name>' . $setting->name . '</name>' . '<value>' . htmlspecialchars($setting->value) . '</value>' . '</userSetting>');
		$writer->write(CRLF);
	} while ($setting->shift());
	$setting->close();
}
$comment = new GuestComment();
if ($comment->open('parent IS NULL')) {
	$writer->write('<guestbook>');
	do {
		if ($comment->isFiltered == 0) {
			$writer->write('<comment>' . '<commenter' . ' id="' . $comment->commenter . '">' . '<name>' . htmlspecialchars(UTF8::correct($comment->name)) . '</name>' . '<homepage>' . htmlspecialchars(UTF8::correct($comment->homepage)) . '</homepage>' . '<ip>' . $comment->ip . '</ip>' . '<openid>' . $comment->openid . '</openid>' . '</commenter>' . '<content>' . htmlspecialchars(UTF8::correct($comment->content)) . '</content>' . '<password>' . htmlspecialchars($comment->password) . '</password>' . '<secret>' . htmlspecialchars($comment->secret) . '</secret>' . '<written>' . $comment->written . '</written>');
			$writer->write(CRLF);
			if ($childComment = $comment->getChildren()) {
				do {
					if ($childComment->isFiltered == 0) {
						$writer->write('<comment>' . '<commenter' . ' id="' . $childComment->commenter . '">' . '<name>' . htmlspecialchars(UTF8::correct($childComment->name)) . '</name>' . '<homepage>' . htmlspecialchars(UTF8::correct($childComment->homepage)) . '</homepage>' . '<ip>' . $childComment->ip . '</ip>' . '<openid>' . $comment->openid . '</openid>' . '</commenter>' . '<content>' . htmlspecialchars(UTF8::correct($childComment->content)) . '</content>' . '<password>' . htmlspecialchars($childComment->password) . '</password>' . '<secret>' . htmlspecialchars($childComment->secret) . '</secret>' . '<written>' . $childComment->written . '</written>' . '</comment>');
						$writer->write(CRLF);
					}
				} while ($childComment->shift());
				$childComment->close();
			}
			$writer->write('</comment>');
			$writer->write(CRLF);
		}
	} while ($comment->shift());
	$writer->write('</guestbook>');
	$writer->write(CRLF);
	$comment->close();
}
$filter = new Filter();
if ($filter->open()) {
	do {
		$writer->write('<filter type="' . $filter->type . '">' . '<pattern>' . htmlspecialchars($filter->pattern) . '</pattern>' . '</filter>');
		$writer->write(CRLF);
	} while ($filter->shift());
	$filter->close();
}
$feed = new Feed();
if ($feed->open()) {
	do {
		$writer->write('<feed>' . '<group>' . htmlspecialchars($feed->getGroupName()) . '</group>' . '<url>' . htmlspecialchars(UTF8::correct($feed->url)) . '</url>' . '</feed>');
		$writer->write(CRLF);
	} while ($feed->shift());
	$feed->close();
}
$writer->write('</blog>');
$writer->close();
if (defined('__TEXTCUBE_BACKUP__')) {
	@chmod(ROOT . "/cache/backup/$blogid.xml", 0666);
	respond::ResultPage(0);
}
?>
