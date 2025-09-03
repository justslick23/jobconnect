<?php

namespace App;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ShortlistingReportExport
{
    protected $reportData;
    protected $spreadsheet;

    public function __construct($reportData)
    {
        $this->reportData = $reportData;
        $this->spreadsheet = new Spreadsheet();
    }

    public function generate(string $fileName = null)
    {
        // Summary sheet
        $this->createSummarySheet();

        // Detailed sheets
        foreach ($this->reportData as $requisitionId => $data) {
            $this->createDetailedSheet($requisitionId, $data);
        }

        // Skills Analysis sheet
        $this->createSkillsAnalysisSheet();

        $writer = new Xlsx($this->spreadsheet);

        if (!$fileName) {
            $fileName = 'shortlisting_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        }

        $path = storage_path("app/public/{$fileName}");
        $writer->save($path);

        return $path;
    }

    protected function createSummarySheet()
    {
        $sheet = $this->spreadsheet->getActiveSheet();
        $sheet->setTitle('Summary');

        $headings = [
            'Requisition ID','Job Title','Job Reference','Total Applications','Shortlisted',
            'Under Review','Rejected','Average Score','Highest Score','Lowest Score',
            'Threshold','Required Skills Count','Min Experience (Years)','Required Education','Shortlisting Rate (%)'
        ];
        $sheet->fromArray($headings, null, 'A1');

        $rowNum = 2;

        foreach ($this->reportData as $requisitionId => $data) {
            $applications = collect($data['applications']);
            $row = [
                $requisitionId,
                $data['job_title'],
                $data['job_reference'],
                $applications->count(),
                $applications->where('new_status','shortlisted')->count(),
                $applications->where('new_status','review')->count(),
                $applications->where('new_status','rejected')->count(),
                round($applications->avg('final_score'),2),
                $applications->max('final_score'),
                $applications->min('final_score'),
                $data['threshold'],
                count($data['required_skills']),
                $data['min_experience'],
                $data['required_education'],
                $applications->count() > 0 ? round(($applications->where('new_status','shortlisted')->count() / $applications->count())*100,1) : 0
            ];

            $sheet->fromArray($row, null, 'A'.$rowNum++);
        }

        // Style header
        $sheet->getStyle('A1:O1')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID,'startColor'=>['rgb'=>'E2EFDA']],
            'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->calculateColumnWidths();
    }

    protected function createDetailedSheet($requisitionId, $data)
    {
        $sheet = new Worksheet($this->spreadsheet, "Job {$requisitionId}");
        $this->spreadsheet->addSheet($sheet);

        $headings = [
            'Application ID','Applicant Name','Email','Application Date','Final Status','Previous Status','Final Score',
            'Skills Score','Experience Score','Education Score','Qualification Bonus','Total Experience (Years)',
            'Meets Min Experience','Experience Gap (Years)','User Skills','Matching Skills Keywords','Missing Skills Keywords',
            'Skills Match %','User Education Level','User Field of Study','Meets Education Level','Meets Field Requirement',
            'Qualifications','Recommendation'
        ];
        $sheet->fromArray($headings, null, 'A1');

        $rowNum = 2;
        foreach ($data['applications'] as $app) {
            $skillMatch = $app['skill_match_details'];
            $educationMatch = $app['education_match_details'];
            $bestEducation = collect($educationMatch['user_education'])->first();
            $userEducationLevel = $bestEducation['level'] ?? 'N/A';
            $userFieldOfStudy = $bestEducation['field'] ?? 'N/A';

            $recommendation = $this->generateRecommendation($app);

            $row = [
                $app['application_id'],$app['applicant_name'],$app['applicant_email'],$app['application_date'],
                ucfirst($app['new_status']),ucfirst($app['old_status']),$app['final_score'],$app['skills_score'],
                $app['experience_score'],$app['education_score'],$app['qualification_bonus'],$app['total_experience_years'],
                $app['meets_minimum_experience'] ? 'Yes':'No',$app['experience_gap'],
                implode('; ',$app['user_skills']),
                implode('; ',$skillMatch['matching_keywords']),
                implode('; ',$skillMatch['missing_keywords']),
                round($skillMatch['match_percentage'],1),
                $userEducationLevel,$userFieldOfStudy,
                $educationMatch['meets_level_requirement'] ? 'Yes':'No',
                $educationMatch['meets_field_requirement'] ? 'Yes':'No',
                implode('; ',$app['user_qualifications']),
                $recommendation
            ];

            $sheet->fromArray($row, null, 'A'.$rowNum++);
        }

        // Style header
        $sheet->getStyle('A1:X1')->applyFromArray([
            'font'=>['bold'=>true],
            'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'D4ECFB']],
            'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER],
        ]);
    }

    protected function createSkillsAnalysisSheet()
    {
        $sheet = new Worksheet($this->spreadsheet, "Skills Analysis");
        $this->spreadsheet->addSheet($sheet);

        $headings = ['Requisition ID','Job Title','Required Skill','Total Applicants','Applicants with Skill','Skill Coverage %','Availability Level'];
        $sheet->fromArray($headings,null,'A1');

        $rowNum = 2;

        foreach($this->reportData as $requisitionId => $data) {
            $applications = collect($data['applications']);
            foreach($data['required_skills'] as $skill){
                $count = $applications->filter(function($app) use ($skill){
                    return collect($app['user_skills'])->contains(fn($s)=>stripos($s,$skill)!==false || stripos($skill,$s)!==false);
                })->count();

                $row = [
                    $requisitionId,
                    $data['job_title'],
                    $skill,
                    $applications->count(),
                    $count,
                    $applications->count() > 0 ? round(($count/$applications->count())*100,1):0,
                    $this->getSkillDemandLevel($count,$applications->count()),
                ];
                $sheet->fromArray($row,null,'A'.$rowNum++);
            }
        }

        $sheet->getStyle('A1:G1')->applyFromArray([
            'font'=>['bold'=>true],
            'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'FFF2CC']],
            'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER],
        ]);
    }

    private function generateRecommendation($app)
    {
        $score = $app['final_score'];
        $status = $app['new_status'];
        $skillMatch = $app['skill_match_details']['match_percentage'];
        $meetsExperience = $app['meets_minimum_experience'];
        $educationMatch = $app['education_match_details'];

        $recommendations = [];

        if ($status === 'shortlisted') $recommendations[] = "Strong candidate - proceed with interview";
        elseif ($status === 'review') $recommendations[] = "Potential candidate - requires manual review";
        else $recommendations[] = "Does not meet minimum requirements";

        if ($skillMatch<30) $recommendations[] = "Lacks key technical skills";
        elseif($skillMatch<60) $recommendations[] = "Has some relevant skills but gaps exist";

        if(!$meetsExperience) $recommendations[] = "Experience gap: {$app['experience_gap']} years below requirement";

        if(!$educationMatch['meets_level_requirement']) $recommendations[] = "Below required education level";
        if(!$educationMatch['meets_field_requirement']) $recommendations[] = "Different field of study";

        return implode('; ',$recommendations);
    }

    private function getSkillDemandLevel($count,$total)
    {
        if($total===0) return 'N/A';
        $p = ($count/$total)*100;
        return $p>=75 ? 'High Availability' : ($p>=50 ? 'Moderate Availability' : ($p>=25?'Low Availability':'Rare Skill'));
    }
}
