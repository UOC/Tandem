<?php

/**
 * Class TandemRecord
 */
class TandemRecord
{
	private $recordId;
	private $meetingId;
	private $name;
	private $isPublished;
	private $state;
	private $startTime;
	private $endTime;
	private $playbackType;
	private $playbackUrl;
	private $presentationUrl;
	private $podcastUrl;
	private $statisticsUrl;
	private $playbackLength;
	private $metas;

	/**
	 * Record constructor.
	 * @param $xml \SimpleXMLElement
	 */
	public function __construct($xml)
	{
		$this->recordId       = $xml->recordID->__toString();
		$this->meetingId      = $xml->meetingID->__toString();
		$this->name           = $xml->name->__toString();
		$this->isPublished    = $xml->published->__toString() === 'true';
		$this->state          = $xml->state->__toString();
		$this->startTime      = (float) $xml->startTime->__toString();
		$this->endTime        = (float) $xml->endTime->__toString();
		foreach ($xml->playback->format as $format) {
			$type = $format->type->__toString();
			switch ($type) {
				case 'presentation':
					$this->presentationUrl = $format->url->__toString();
					break;
				case 'video':
					$this->playbackType = $type;
					$this->playbackUrl = $format->url->__toString();
					break;
				case 'podcast':
					$this->podcastUrl = $format->url->__toString();
					break;
				case 'statistics':
					$this->statisticsUrl = $format->url->__toString();
					break;
			}

		}
		$this->playbackLength = (int) $xml->playback->format->length->__toString();

		foreach ($xml->metadata->children() as $meta) {
			$this->metas[$meta->getName()] = $meta->__toString();
		}
	}

	/**
	 * @return string
	 */
	public function getRecordId()
	{
		return $this->recordId;
	}

	/**
	 * @return string
	 */
	public function getMeetingId()
	{
		return $this->meetingId;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return boolean
	 */
	public function isPublished()
	{
		return $this->isPublished;
	}

	/**
	 * @return string
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * @return string
	 */
	public function getStartTime()
	{
		return $this->startTime;
	}

	/**
	 * @return string
	 */
	public function getEndTime()
	{
		return $this->endTime;
	}

	/**
	 * @return string
	 */
	public function getPlaybackType()
	{
		return $this->playbackType;
	}

	/**
	 * @return string
	 */
	public function getPlaybackUrl()
	{
		return $this->playbackUrl;
	}
	/**
	 * @return string
	 */
	public function getStatisticsUrl()
	{
		return $this->statisticsUrl;
	}
	/**
	 * @return string
	 */
	public function getPodcastUrl()
	{
		return $this->podcastUrl;
	}
	/**
	 * @return string
	 */
	public function getPresentationUrl()
	{
		return $this->presentationUrl;
	}

	/**
	 * @return string
	 */
	public function getPlaybackLength()
	{
		return $this->playbackLength;
	}

	/**
	 * @return array
	 */
	public function getMetas()
	{
		return $this->metas;
	}
}
