<?php

class App_View_Helper_Nomenclature extends Zend_View_Helper_Abstract
{

    /**
     *
     * @return \App_View_Helper_Nomenclature
     */
    public function nomenclature()
    {
        return $this;
    }

    /**
     *
     * @param string $value
     * @return string
     */
    public function scholarityClassification($value)
    {
        $name = '';
        switch ($value) {
            case 'R':
                $name = 'Rejistrasaun';
                break;
            case 'A':
                $name = 'Akreditasaun';
                break;
            case 'C':
                $name = 'Formasaun Comunitaria';
                break;
            default:
                $name = 'La iha';
        }

        return $name;
    }

    /**
     *
     * @param string $value
     * @return string
     */
    public function activeStatus($value)
    {
        $name = '';
        switch ($value) {
            case '1':
                $name = 'Loke';
                break;
            case '0':
                $name = 'Taka';
                break;
            case '2':
                $name = 'Kansela';
                break;
            default:
                $name = 'La iha';
        }

        return $name;
    }

    /**
     *
     * @param string $value
     * @return string
     */
    public function scholarityCategory($value)
    {
        $name = '';
        switch ($value) {
            case 'S':
                $name = 'Eskola';
                break;
            case 'N':
                $name = 'Formasaun Teknika Profisional';
                break;
            case 'U':
                $name = 'Superior';
                break;
            case 'C':
                $name = 'Formasaun Comunitaria';
                break;
            case 'V':
                $name = 'Formasaun Profisional';
                break;
            default:
                $name = 'La iha';
        }

        return $name;
    }

    /**
     *
     * @param string $value
     * @return string
     */
    public function typeBudgetCategory($value)
    {
        $name = '';
        switch ($value) {
            case 'E':
                $name = 'Depesas';
                break;
            case 'I':
                $name = 'Receitas';
                break;
            case 'S':
                $name = 'Inicial';
                break;
            case 'A':
                $name = 'Anual';
                break;
                break;
            default:
                $name = 'La iha';
        }

        return $name;
    }

    /**
     *
     * @param string $value
     * @return string
     */
    public function resultClass($value)
    {
        $name = '';
        switch ($value) {
            case StudentClass_Model_Mapper_StudentClass::ENROLLED:
                $name = 'Rejistadu';
                break;
            case StudentClass_Model_Mapper_StudentClass::DROPPED_OUT:
                $name = 'Retira';
                break;
            case StudentClass_Model_Mapper_StudentClass::COMPLETED:
                $name = 'Seidauk Kompetente';
                break;
            case StudentClass_Model_Mapper_StudentClass::GRADUATED:
                $name = 'Kompetente';
                break;
            case StudentClass_Model_Mapper_StudentClass::NO_MANDATORY:
                $name = 'La aplika';
                break;
            default:
                $name = 'La iha';
        }

        return $name;
    }

    /**
     *
     * @param string $value
     * @return string
     */
    public function statusCase($value)
    {
        $name = '';
        switch ($value) {
            case Client_Model_Mapper_Case::BARRIER_PENDING:
                $name = 'Seidauk hotu';
                break;
            case Client_Model_Mapper_Case::BARRIER_NOT_COMPLETED:
                $name = 'Laos hotu';
                break;
            case Client_Model_Mapper_Case::BARRIER_COMPLETED:
                $name = 'Kompletu';
                break;
            default:
                $name = 'La iha';
        }

        return $name;
    }

    /**
     *
     * @param string $value
     * @return string
     */
    public function levelScholarity($value)
    {
        $name = '';
        switch ($value) {
            case '1':
                $name = 'ALFABETIZASAUN';
                break;
            case '2':
                $name = 'PRIMARIU';
                break;
            case '3':
                $name = 'PRE-SECUNDARIU';
                break;
            case '4':
                $name = 'SECUNDARIU';
                break;
            case '41':
                $name = 'SECUNDARIU TEKNIKU';
                break;
            case '6':
                $name = 'SUPERIOR';
                break;
            case '7':
                $name = 'POS-GRADUASAUN';
                break;
            default:
                $name = 'La iha';
        }

        return $name;
    }

    /**
     *
     * @param string $value
     * @return string
     */
    public function drhModality($value)
    {
        $name = '';
        switch ($value) {
            case 'L':
                $name = 'Iha rai laran no ho entidade nasionál sira';
                break;
            case 'A':
                $name = 'Iha rai liur';
                break;
            case 'T':
                $name = 'Iha rai laran ho entidade formadora husi rai liur ';
                break;
            default:
                $name = 'La iha';
        }

        return $name;
	}
	
	/**
     *
     * @param string $value
     * @return string
     */
    public function responsible($value)
    {
        $name = '';
        switch ($value) {
            case Client_Model_Mapper_Case::RESPONSIBLE_CLIENT:
                $name = 'Kliente';
				break;
			case Client_Model_Mapper_Case::RESPONSIBLE_COUNSELLOR:
                $name = 'Konselleru';
                break;
            default:
                $name = 'La iha';
        }

        return $name;
	}
}
