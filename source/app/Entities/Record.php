<?php declare(strict_types=1);

namespace App\Entities;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\BelongsTo;

/**
 * @Entity(table="records")
 */
class Record
{
    /**
     * @Column(type="primary")
     * @var int
     */
    protected $id;

    /**
     * @Column(type="string")
     * @var string
     */
    protected $summary;

    /**
     * @Column(type="decimal(5,3)")
     * @var decimal
     */
    protected $gradeValue;

    /**
     * @Column(type="boolean", default="false")
     * @var boolean
     */
    protected $verified;

    /**
     * @BelongsTo(target="Discipline", cascade=false)
     */
    protected $discipline;

    /**
     * @BelongsTo(target="Subject", cascade=false)
     */
    protected $subject;

    /**
     * @BelongsTo(target="Level", cascade=false)
     */
    protected $level;

    /**
     * @Column(type="datetime", nullable=true)
     * @var datetime
     */
    protected $created_at;

    /**
     * @Column(type="datetime", fkAction="NO ACTION", nullable=true)
     * @var datetime
     */
    protected $updated_at;

    /**
     * The constructor
     */
    public function __construct()
    {
        $this->verified = false;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function setSummary(string $summary): void
    {
        $this->summary = $summary;
    }

    public function getGradeValue(): float
    {
        return $this->gradeValue;
    }

    public function setGradeValue(float $gradeValue): void
    {
        $this->gradeValue = $gradeValue;
    }

    public function getVerified(): boolean
    {
        return $this->verified;
    }

    public function setVerified(boolean $verified): void
    {
        $this->verified = $verified;
    }

    public function getDiscipline(): Discipline
    {
        return $this->discipline;
    }

    public function setDiscipline(Discipline $discipline): void
    {
        $this->discipline = $discipline;
    }

    public function getSubject(): Subject
    {
        return $this->subject;
    }

    public function setSubject(Subject $subject): void
    {
        $this->subject = $subject;
    }

    public function getLevel(): Level
    {
        return $this->level;
    }

    public function setLevel(Level $level): void
    {
        $this->level = $level;
    }
}