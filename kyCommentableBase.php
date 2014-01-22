<?php
/**
 * Base class for Kayako objects which can be commented.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @since Kayako version 4.51.1891
 * @package Object\Base
 */
abstract class kyCommentableBase extends kyObjectBase {

	/**
	 * Name of class representing comments for this object.
	 * @var string
	 */
	static protected $comment_class = null;

	/**
	 * Comments for this object.
	 * @var kyCommentBase[]
	 */
	protected $comments;

	/**
	 *
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return kyCommentBase[]|kyResultSet
	 */
	public function getComments($reload = false) {
		if ($this->comments !== null && !$reload)
			return $this->comments;

		$id = $this->getId();
		if ($id === null)
			return null;

		$classname = static::$comment_class;
		/** @noinspection PhpUndefinedMethodInspection */
		$this->comments = $classname::getAll($id);
		return new kyResultSet($this->comments);
	}

	/**
	 * Creates a new comment for this object.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param kyUser|kyStaff|string $creator Creator (staff object, user object or user fullname) of this comment.
	 * @param string $contents Contents of this comment.
	 * @return kyCommentBase
	 */
	public function newComment($creator, $contents) {
		$classname = static::$comment_class;
		/** @noinspection PhpUndefinedMethodInspection */
		return $classname::createNew($this, $creator, $contents);
	}
}