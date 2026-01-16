<?php

class fun
{
    private $db;

    public function __construct($conn)
    {
        $this->db = $conn;
    }

    // Delete all store logo before insert
    public function deleteLogo()
    {
        $sql = "DELETE FROM `brand`";
        $result = $this->db->query($sql);
        return $result;
    }

    // Insert function of logo 
    public function logoInsert($logo_name, $logo_path, $logo_type, $brand_name)
    {
        $sql = "INSERT INTO `brand`(`logo_name`, `logo_path`, `logo_type`, `brand_name`) VALUES ('$logo_name','$logo_path','$logo_type','$brand_name')";

        $result = $this->db->query($sql);
        return $result;
    }

    // Fetch logo function
    public function fetchLogo()
    {
        $sql = "SELECT * FROM `brand`";
        $result = $this->db->query($sql);
        return $result;
    }

    // insert carousel function 
    public function carouselInsert($slide_title, $slide_content, $bg_img_name, $bg_img_path, $bg_img_type, $logo_name, $logo_path, $logo_type)
    {
        $sql = "INSERT INTO `slider`(`slide_title`, `slide_content`, `bg_img_name`, `bg_img_path`, `bg_img_type`, `logo_name`, `logo_path`, `logo_type`) VALUES ('$slide_title','$slide_content','$bg_img_name','$bg_img_path','$bg_img_type','$logo_name','$logo_path','$logo_type')";

        $result = $this->db->query($sql);
        return $result;
    }

    // Update carousel function
    public function updateCarousel($id, $slide_title, $slide_content, $bg_img_name, $bg_img_path, $bg_img_type, $logo_name, $logo_path, $logo_type)
    {
        $sql = "UPDATE `slider` SET `slide_title`='$slide_title',`slide_content`='$slide_content',`bg_img_name`='$bg_img_name',`bg_img_path`='$bg_img_path',`bg_img_type`='$bg_img_type',`logo_name`='$logo_name',`logo_path`='$logo_path',`logo_type`='$logo_type' WHERE slide_id = $id";

        $result = $this->db->query($sql);
        return $result;
    }

    // Select all data from carousel table function
    public function carousel_fetch_all_data()
    {
        $sql = "SELECT * FROM `slider`";

        $result = $this->db->query($sql);
        return $result;
    }

    // Select single data from carousel table function
    public function carousel_fetch_single_data($id)
    {
        $sql = "SELECT * FROM `slider` WHERE `slide_id` = $id";

        $result = $this->db->query($sql);
        return $result;
    }

    // project insert functioning
    public function projectInsert($projectname, $projectsummary, $projectimg_name, $img_location, $img_type)
    {
        $sql = "INSERT INTO `projects`(`project_name`, `project_summary`, `image_name`, `image_path`, `img_type`) VALUES ('$projectname','$projectsummary','$projectimg_name','$img_location','$img_type')";

        $result = $this->db->query($sql);
        return $result;
    }

    // project update functioning
    public function projectUpdate($id, $projectname, $projectsummary, $projectimg_name, $img_location, $img_type)
    {
        $sql = "UPDATE `projects` SET `project_name`='$projectname',`project_summary`='$projectsummary',`image_name`='$projectimg_name',`image_path`='$img_location',`img_type`='$img_type' WHERE `id` = $id";

        $result = $this->db->query($sql);
        return $result;
    }

    // Fetch all project functioning
    public function projectFetch()
    {
        $sql = "SELECT * FROM `projects`";

        $result = $this->db->query($sql);
        return $result;
    } 

    // search project functuioning
    public function searchProject($value)
    {
        $sql = "SELECT * FROM `projects` WHERE `project_name`LIKE '%$value%' ";

         $result = $this->db->query($sql);
        return $result;
    }

    // Fetch all project functioning
    public function singleProjectFetch($id)
    {
        $sql = "SELECT * FROM `projects` WHERE `id` = $id";

        $result = $this->db->query($sql);
        return $result;
    } 
}
