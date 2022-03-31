<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Magazine;
use App\Entity\User;
use DateTime;

class StatsViewsRepository extends StatsRepository
{
    public function getOverallStats(
        User $user = null,
        Magazine $magazine = null
    ): array {
        $this->user     = $user;
        $this->magazine = $magazine;

        return $this->sort($this->getMonthlyStats());
    }

    public function getStatsByTime(DateTime $start, ?User $user, ?Magazine $magazine): array
    {
        $this->start    = $start;
        $this->user     = $user;
        $this->magazine = $magazine;

        return $this->prepareContentDaily($this->getDailyStats());
    }

    private function getMonthlyStats(): array
    {
        $conn = $this->getEntityManager()
            ->getConnection();

        if ($this->user) {
            $sql = "SELECT to_char(e.created_at,'Mon') as month, extract(year from e.created_at) as year, SUM(e.views) as count FROM entry e 
                    WHERE e.user_id = ".$this->user->getId()." GROUP BY 1,2";
        } elseif ($this->magazine) {
            $sql = "SELECT to_char(e.created_at,'Mon') as month, extract(year from e.created_at) as year, SUM(e.views) as count FROM entry e 
                    WHERE e.magazine_id = ".$this->magazine->getId()." GROUP BY 1,2";
        } else {
            $sql = "SELECT to_char(e.created_at,'Mon') as month, extract(year from e.created_at) as year, SUM(e.views) as count 
                    FROM entry e GROUP BY 1,2";
        }

        $stmt = $conn->prepare($sql);
        $stmt = $stmt->executeQuery();

        return array_map(fn($val) => [
            'month' => date_parse($val['month'])['month'],
            'year'  => (int) $val['year'],
            'count' => (int) $val['count'],
        ], $stmt->fetchAllAssociative());
    }

    private function getDailyStats(): array
    {
        $conn = $this->getEntityManager()
            ->getConnection();

        if ($this->user) {
            $sql = "SELECT  date_trunc('day', e.created_at) as day, SUM(e.views) as count FROM entry e 
                    WHERE e.created_at >= '".$this->start->format('Y-m-d H:i:s')."' 
                    AND e.user_id = ".$this->user->getId()." GROUP BY 1";
        } elseif ($this->magazine) {
            $sql = "SELECT  date_trunc('day', e.created_at) as day, SUM(e.views) as count FROM entry e 
                    WHERE e.created_at >= '".$this->start->format('Y-m-d H:i:s')."' 
                    AND e.magazine_id = ".$this->magazine->getId()." GROUP BY 1";
        } else {
            $sql = "SELECT  date_trunc('day', e.created_at) as day, SUM(e.views) as count FROM entry e 
                    WHERE e.created_at >= '".$this->start->format('Y-m-d H:i:s')."' GROUP BY 1";
        }

        $stmt = $conn->prepare($sql);
        $stmt = $stmt->executeQuery();

        $results = $stmt->fetchAllAssociative();

        usort($results, fn($a, $b): int => $a['day'] <=> $b['day']);

        return $results;
    }
}
