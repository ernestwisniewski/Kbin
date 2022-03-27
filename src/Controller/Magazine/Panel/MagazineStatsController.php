<?php declare(strict_types=1);

namespace App\Controller\Magazine\Panel;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Repository\StatsRepository;
use App\Service\StatsManager;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MagazineStatsController extends AbstractController
{
    public function __construct(private StatsManager $manager)
    {
    }

    /**
     * @IsGranted("ROLE_USER")
     * @IsGranted("edit", subject="magazine")
     */
    public function __invoke(Magazine $magazine, ?string $type, ?int $period, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit_profile', $this->getUserOrThrow());

        if ($period) {
            $period = min($period, 256);
            $start  = (new DateTime())->modify("-$period days");
        }

        return $this->render(
            'magazine/panel/front.html.twig', [
                'period'       => $request->get('period'),
                'magazine'     => $magazine,
                'contentChart' => $period
                    ? $this->manager->drawDailyStatsByTime($start, null, $magazine)
                    : $this->manager->drawMonthlyChart(null, $magazine),
            ]
        );
    }
}

