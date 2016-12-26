<?php

/**
 * Description: Location Service
 * get, set, update location for profile
 * Modify History:
 *  September 10, 2015: luongmh initial version
 */

namespace Gcs\Repository;

class LocationRepository
{

    const ERR_ORG_NOT_EXIST = "E2007";
    const ERR_LOCATION_EXIST = "E2004";
    const ERR_ADD_DATA = "E2005";
    const ERR_DELETE_DATA = "E2006";

    /*
     * Get all locations
     * @param sfWebRequest $request
     * @return array result
     */

    function getLocationList($request)
    {
        $columns = array(
            array('db' => 'd.id', 'dt' => 0),
            array('db' => 'd.organization', 'dt' => 1, 'is_search' => true),
            array('db' => 'd.location', 'dt' => 2, 'is_search' => true)
        );

        $limit = \SSP::limit($request);
        $order = \SSP::order($request, $columns);
        $where = \SSP::filter($request, $columns);
        $query = \LocationTable::getInstance()->createQuery('d');
        $query = $query->limit($limit['limit'])->offset($limit['offset']);
        foreach ($where as $key => $val) {
            $query = $query->orWhere($key . ' ILIKE ?', '%' . $val . '%');
        }
        foreach ($order as $orderBy) {
            $query = $query->addOrderBy($orderBy);
        }
        return array('result' => $query->execute(), 'count' => $query->count());
    }

    /*
     * Check if location existed
     * @param [String] organization and location
     * @return true if not existed or error message
     */

    function checkExisted($org, $location)
    {
        $table = \LocationTable::getInstance();
        $query = $table->createQuery()
                        ->where('lower(organization) = ?', strtolower($org))
                        ->andwhere('lower(location) = ?', strtolower($location))->fetchOne();
        if ($query) {
            return self::ERR_LOCATION_EXIST;
        }
        return FALSE;
    }

    /*
     * Add or edit location
     * @param sfWebRequest $request
     * @return location object if success or error message
     */

    function addNewLocation($request)
    {
        $new = !($request->getParameter('id'));
        $org = $request->getParameter('organization');
        $location = $request->getParameter('location');

        $isExist = $this->checkExisted($org, $location);
        if ($isExist !== FALSE) {
            return $isExist;
        }

        if ($new) {
            $instance = new \Location();
        } else {
            $instance = $this->getLocationById($request->getParameter('id'));
        }

        $instance->setOrganization($org)->setLocation($location)->save();
        if (!$instance) {
            return self::ERR_ADD_DATA;
        }
        return $instance;
    }

    /*
     * Delete a location
     * @param [integer] location Id
     * @return TRUE if success or error message
     */

    function deleteLocation($id)
    {
        $location = $this->getLocationById($id);
        if (!is_object($location)) {
            return $location;
        }

        $delete = $location->delete();
        if (!$delete) {
            return self::ERR_DELETE_DATA;
        }
        return $delete;
    }

    /*
     * Get all organizations and location to speific organizaiton
     * @param [string] Organization's name
     * @return [array] contain all organizations and locations belong to
     */

    function getAllOrgAndLocation($org)
    {
        $result = array();
        $tmp = array();

        $organization = $this->getDistinctOrganization();

        // Get organization's original value
        if (is_object($organization)) {
            foreach ($organization as $value) {
                if (!preg_grep("/" . $value->getOrg() . "/i", $tmp)) {
                    array_push($tmp, $value->getOrg());
                }
            }
        }
        $result['organization'] = $tmp;

        // Get location by device's organiztion or first organization
        if ($org) {
            $local = $this->getLocationByOrg($org);
        } else {
            $local = $this->getLocationByOrg($tmp[0]);
        }

        //Get location name only
        $tmp = array();
        if (is_object($local)) {
            foreach ($local as $value) {
                array_push($tmp, $value->getLocation());
            }
        }
        $result['location'] = $tmp;
        return $result;
    }

    /*
     * Get distinct organization
     * @return [object] contain distinct organization
     */

    function getDistinctOrganization()
    {
        $locationTable = \LocationTable::getInstance();
        $organization = $locationTable->createQuery('a')
                        ->select('distinct(a.organization) as org')
                        ->orderBy('org')->execute();
        if (!$organization) {
            return false;
        }
        return $organization;
    }

    /*
     * Get all locations of specific organization
     * @param [string] Organization's name
     * @return [object] list of location
     */

    function getLocationByOrg($org)
    {
        $table = \LocationTable::getInstance();
        $query = $table->createQuery('a')->where('lower(a.organization) = ?', strtolower($org))
                        ->orderBy('a.location')->execute();
        if (!$query) {
            return self::ERR_ORG_NOT_EXIST;
        }
        return $query;
    }

    /*
     * Get location by Id
     * @param [integer] location Id
     * @return [object] location if found
     */

    function getLocationById($id)
    {
        $query = \LocationTable::getInstance()->findOneById(intval($id));
        if (!$query) {
            return self::ERR_ORG_NOT_EXIST;
        }
        return $query;
    }

}
