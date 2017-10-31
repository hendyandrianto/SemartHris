<?php

namespace KejawenLab\Application\SemartHris\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController;
use KejawenLab\Application\SemartHris\Component\Attendance\Service\AttendanceImporter;
use KejawenLab\Application\SemartHris\Form\Manipulator\AttendanceManipulator;
use KejawenLab\Application\SemartHris\Repository\AttendanceRepository;
use League\Csv\Reader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Muhamad Surya Iksanudin <surya.iksanudin@kejawenlab.com>
 */
class AttendanceController extends AdminController
{
    /**
     * @Route("/attendance/upload", name="upload_attendance")
     *
     * @param Request $request
     */
    public function uploadAttendanceAction(Request $request)
    {
        /** @var Reader $processor */
        $processor = Reader::createFromPath($request->files->get('attendance'));
        $processor->setHeaderOffset(0);

        $importer = $this->container->get(AttendanceImporter::class);
        $importer->import($processor->getRecords());
        exit();
    }

    /**
     * @param string $entityClass
     * @param string $sortDirection
     * @param null   $sortField
     * @param null   $dqlFilter
     *
     * @return array
     */
    protected function createListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null)
    {
        $startDate = \DateTime::createFromFormat('d-m-Y', $this->request->query->get('startDate', date('01-m-Y')));
        $endDate = \DateTime::createFromFormat('d-m-Y', $this->request->query->get('endDate', date('t-m-Y')));
        $companyId = $this->request->get('company');
        $departmentId = $this->request->get('department');
        $shiftmentId = $this->request->get('shiftment');

        return $this->container->get(AttendanceRepository::class)->getFilteredAttendance($startDate, $endDate, $companyId, $departmentId, $shiftmentId, [$sortField => $sortDirection]);
    }

    /**
     * @param object $entity
     * @param string $view
     *
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    protected function createEntityFormBuilder($entity, $view)
    {
        $builder = parent::createEntityFormBuilder($entity, $view);

        return $this->container->get(AttendanceManipulator::class)->manipulate($builder, $entity);
    }
}
