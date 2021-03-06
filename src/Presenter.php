<?php namespace Sofa\Revisionable;

/**
 * @method mixed old(string $key)
 * @method mixed new(string $key)
 * @method mixed diff(string $key)
 */
abstract class Presenter
{
    /**
     * Old attributes.
     *
     * @var array
     */
    protected $old = [];

    /**
     * New attributes.
     *
     * @var array
     */
    protected $new = [];

    /**
     * Revision diff.
     *
     * @var array
     */
    protected $diff = [];

    /**
     * Revision array.
     *
     * @var array
     */
    protected $revision;
   
    /**
     * Create a new revision presenter.
     *
     * @param array $revision
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $revision)
    {
        $this->revision = $revision;

        $this->boot();
    }

    /**
     * Render diff.
     *
     * @return string
     */
    abstract public function renderDiff();

    /**
     * Get revision diff.
     *
     * @return array
     */
    public function getDiff()
    {
        return $this->diff;
    }

    /**
     * Get item from revision array.
     *
     * @param  string $key
     * @return mixed
     */
    public function getFromRevision($key)
    {
        return $this->getFromArray($this->revision, $key);
    }

    /**
     * Get item from array with fallback to null.
     *
     * @param  array  $array [description]
     * @param  string $key
     * @return mixed
     */
    protected function getFromArray(array $array, $key)
    {
        return (array_key_exists($key, $array))
            ? $array[$key]
            : null;
    }

    /**
     * Boot presenter.
     *
     * @return void
     */
    protected function boot()
    {
        $this->parseRevision();
    }

    /**
     * Decode revision and build diff.
     *
     * @return void
     */
    protected function parseRevision()
    {
        $this->old = (array) json_decode($this->getFromRevision('old'));

        $this->new = (array) json_decode($this->getFromRevision('new'));

        $this->buildDiff();
    }

    /**
     * Build revision diff.
     *
     * @return void
     */
    protected function buildDiff()
    {
        foreach ($this->getUpdated() as $key) {
            $this->diff[$key]['old'] = $this->old($key);

            $this->diff[$key]['new'] = $this->new($key);
        }
    }

    /**
     * Get updated attributes array.
     *
     * @return array
     */
    public function getUpdated()
    {
        return array_keys(array_diff_assoc($this->new, $this->old));
    }

    /**
     * Handle dynamic properties.
     *
     * @param  string
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getFromRevision($key);
    }

    /**
     * Handle dynamic method calls.
     *
     * @param  string
     * @param  array
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if ( ! is_array($this->$method)) {
            return null;
        }

        array_unshift($parameters, $this->$method);

        return call_user_func_array(array($this, 'getFromArray'), $parameters);
    }

    /**
     * Handle casting to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->renderDiff();
    }
}
