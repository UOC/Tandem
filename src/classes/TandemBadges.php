<?php


class TandemBadges {

	const LEVEL_TANDEM_LICENSE = 1;
	const LEVEL_TANDEM_LICENSE_IMAGE = 'badge_level_tandem_license.png';
	const LEVEL_NOVICE = 2;
	const LEVEL_NOVICE_IMAGE = 'badge_level_novice.png';
	const LEVEL_GRADUATE = 3;
	const LEVEL_GRADUATE_IMAGE = 'badge_level_graduate.png';
	const LEVEL_SUPER_SPEAKER = 4;
	const LEVEL_SUPER_SPEAKER_IMAGE = 'badge_level_super_speaker.png';
	const LEVEL_MASTER_TALK = 5;
	const LEVEL_MASTER_TALK_IMAGE = 'badge_level_master_talk.png';
	const LEVEL_TANDEM_LEGEND = 6;
	const LEVEL_TANDEM_LEGEND_IMAGE = 'badge_level_tandem_legend.png';

	const FEEDBACK_EXPERT_PARTNER_RATE = 5;
	const FEEDBACK_EXPERT_TOTAL = 12;
	const NUM_TANDEMS_SAME_PERSON = 12;
	const NUM_TANDEMS_SOCIAL_PERSON = 12;
	const NUM_FORMS_COMPLETED = 2;


	/**
	 * Get level badge
	 * @param $done_intro_quiz
	 * @param $num_tandems_with_feedback
	 * @param $minutes_with_feedback
	 *
	 * @return bool|int
	 */
	public static function get_level_badge($done_intro_quiz, $num_tandems_with_feedback, $minutes_with_feedback) {

		$level = false;
		if ($done_intro_quiz) {
			if ($num_tandems_with_feedback >= 15 && $minutes_with_feedback >= 300) {
				$level = self::LEVEL_TANDEM_LEGEND;
			} elseif ($num_tandems_with_feedback >= 12 && $minutes_with_feedback >= 240) {
				$level = self::LEVEL_MASTER_TALK;
			} elseif ($num_tandems_with_feedback >= 9 && $minutes_with_feedback >= 180) {
				$level = self::LEVEL_SUPER_SPEAKER;
			} elseif ($num_tandems_with_feedback >= 6 && $minutes_with_feedback >= 120) {
				$level = self::LEVEL_GRADUATE;
			} elseif ($num_tandems_with_feedback >= 3 && $minutes_with_feedback >= 60) {
				$level = self::LEVEL_NOVICE;
			} else {
				$level = self::LEVEL_TANDEM_LICENSE;
			}
		}
		return $level;
	}

	/**
	 * Get badge by $level
	 * @param $level
	 *
	 * @return bool|string
	 */
	public static function get_level_badge_image($level) {
		$ret = false;
		switch ($level) {
			case self::LEVEL_TANDEM_LICENSE:
				$ret = self::LEVEL_TANDEM_LICENSE_IMAGE;
				break;
			case self::LEVEL_NOVICE:
				$ret = self::LEVEL_NOVICE_IMAGE;
				break;
			case self::LEVEL_GRADUATE:
				$ret = self::LEVEL_GRADUATE_IMAGE;
				break;
			case self::LEVEL_SUPER_SPEAKER:
				$ret = self::LEVEL_SUPER_SPEAKER_IMAGE;
				break;
			case self::LEVEL_MASTER_TALK:
				$ret = self::LEVEL_MASTER_TALK_IMAGE;
				break;
			case self::LEVEL_TANDEM_LEGEND:
				$ret = self::LEVEL_TANDEM_LEGEND_IMAGE;
				break;
		}
		return $ret;
	}

	/**
	 * Check if current user is feedback expert
	 * @param GestorBD $gestorBD
	 * @param $user_id
	 * @param $course_id
	 *
	 * @return bool
	 */
	public static function is_feedback_expert(GestorBD $gestorBD, $user_id, $course_id) {

		$rows = $gestorBD->get_all_my_feedback_ratings( $user_id, $course_id );
		if ($rows) {
			$total = 0;
			foreach ($rows as $row) {
				$data = unserialize( $row['rating_partner_feedback_form'] );
				if ( ! empty( $data->partner_rate ) ) {
					if ($data->partner_rate >= self::FEEDBACK_EXPERT_PARTNER_RATE) {
						$total++;
						if ($total == self::FEEDBACK_EXPERT_TOTAL) {
							return true;
						}
					}
				}
			}
		}
		return false;
	}


	public static function check_badge_criteria($field, GestorBD $gestorBD, $user_id, $course_id) {

		$ret = false;
		switch ($field) {
			case 'badge_feedback_expert':
				$ret = self::is_feedback_expert($gestorBD, $user_id, $course_id);
				break;
			case 'badge_loyalty':
				$ret = self::is_loyalty_partner($gestorBD, $user_id, $course_id);
				break;
			case 'badge_social':
				$ret = self::is_social_partner($gestorBD, $user_id, $course_id);
				break;
			case 'badge_forms':
				$ret = self::all_forms_completed($gestorBD, $user_id, $course_id);
				break;
		}
		return $ret;
	}


	/**
	 *
	 * Done 12 tandems with same person
	 * @param GestorBD $gestorBD
	 * @param $user_id
	 * @param $course_id
	 *
	 * @return bool
	 */
	public static function is_loyalty_partner(GestorBD $gestorBD, $user_id, $course_id) {
		$rows = $gestorBD->get_num_tandems_by_person($course_id, $user_id);
		if ($rows) {
			foreach ($rows as $row) {
				if ($row['total'] >= self::NUM_TANDEMS_SAME_PERSON) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Done 12 tandems with disticnt partners
	 * @param GestorBD $gestorBD
	 * @param $user_id
	 * @param $course_id
	 *
	 * @return bool
	 */
	public static function is_social_partner(GestorBD $gestorBD, $user_id, $course_id) {
		$rows = $gestorBD->get_num_tandems_by_person($course_id, $user_id);
		if ($rows) {
			if (count($rows) >= self::NUM_TANDEMS_SOCIAL_PERSON) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Returns if all forms completed
	 * @param GestorBD $gestorBD
	 * @param $user_id
	 * @param $course_id
	 *
	 * @return bool
	 */
	public static function all_forms_completed(GestorBD $gestorBD, $user_id, $course_id) {
		$surveystatus       = $gestorBD->get_user_survey_status( $user_id, $course_id );
		$total = 0;
		foreach ( $surveystatus as $completed ) {

			if ( $completed ) {
				$total++;
				if ($total >= self::NUM_FORMS_COMPLETED) {
					return true;
				}
			}

		}
		return false;

	}

}