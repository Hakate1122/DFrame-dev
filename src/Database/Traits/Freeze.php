<?php
namespace DLight\Database\Traits;

/**
 * **Freeze Trait**
 * 
 * This trait provides functionality to mark an object as frozen,
 * preventing further modifications to its state.
 */
trait Freeze
{
    /**
     * @var bool
     */
    protected $frozen = false;

    /**
     * @var string|null
     */
    protected $frozen_at;

    /**
     * Freeze the object.
     * 
     * @param bool $saveToDb If true, update frozen_at in DB; if false, only set property in object.
     * @return $this
     */
    public function freeze(bool $saveToDb = false)
    {
        if ($this->isFrozen()) {
            return $this;
        }
        $this->frozen = true;
        if ($saveToDb) {
            $this->frozen_at = date('Y-m-d H:i:s');
            $this->save();
        }
        return $this;
    }

    /**
     * Check if the object is frozen
     *
     * @return bool
     */
    public function isFrozen()
    {
        return $this->frozen ?? false;
    }
}
