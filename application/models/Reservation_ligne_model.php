<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Reservation_ligne_Model extends CI_Model{

  protected $table = 'lignereservation';

  /*

  SELECT NumLigneRes,lr.NumReservation,DateReservation,StatusLigneRes,QteLigneRes,LibelleProduit,lr.IdBoutique, DATE_ADD(DateReservation, INTERVAL p.DureeReservation DAY) as DateFinRes
  FROM lignereservation lr inner join reservation r on lr.NumReservation = r.NumReservation
  inner join produit p on lr.CodeProduit = p.CodeProduit
  where lr.IdBoutique = ?
  and lr.StatusLigneRes = "traite"
  order by DateReservation asc
  */
  public function getResPrepared($id){
    return $this->db->select('NumLigneRes,lr.NumReservation,DateReservation,StatusLigneRes,QteLigneRes,LibelleProduit,DATE_ADD(DateReservation, INTERVAL p.DureeReservation DAY) as DateFinRes')
                    ->from('lignereservation as lr')
                    ->join('reservation as r', 'lr.NumReservation = r.NumReservation')
                    ->join('produit as p', 'lr.CodeProduit = p.CodeProduit')
                    ->where('lr.IdBoutique', $id)
                    ->where('lr.StatusLigneRes', 'traite')
                    ->order_by('DateReservation', 'ASC')
                    ->get()
                    ->result();
  }

  /*
    Retourne les reservations non traitees et non expirees pour une boutique donnee

    SELECT NumLigneRes,lr.NumReservationDateReservation,StatusLigneRes,QteLigneRes,LibelleProduit,DATE_ADD(DateReservation, INTERVAL p.DureeReservation DAY) as DateFinRes
      FROM lignereservation lr
      inner join reservation r on lr.NumReservation = r.NumReservation
      inner join produit p on lr.CodeProduit = p.CodeProduit
      where lr.IdBoutique = ?
      and lr.StatusLigneRes = "non traite"
      and DATE_ADD(DateReservation, INTERVAL p.DureeReservation DAY) >= CURRENT_TIMESTAMP()
      order by DateReservation asc
  */
  public function getResNotPrepared($id){
    return $this->db->select('NumLigneRes,lr.NumReservation,DateReservation,StatusLigneRes,QteLigneRes,LibelleProduit,DATE_ADD(DateReservation, INTERVAL p.DureeReservation DAY) as DateFinRes')
                    ->from('lignereservation as lr')
                    ->join('reservation as r', 'lr.NumReservation = r.NumReservation')
                    ->join('produit as p', 'lr.CodeProduit = p.CodeProduit')
                    ->where('lr.IdBoutique', $id)
                    ->where('lr.StatusLigneRes', 'non traite')
                    ->where('DATE_ADD(DateReservation, INTERVAL p.DureeReservation DAY) >=', date("Y-m-d H:i:s"))
                    ->order_by('DateReservation', 'ASC')
                    ->get()
                    ->result();
  }

  /*
  Retourne les reservations expirees pour une boutique donnee ( pre-condition : status = non traite)

  SELECT NumLigneRes,lr.NumReservation,DateReservation,StatusLigneRes,QteLigneRes,LibelleProduit, DATE_ADD(DateReservation, INTERVAL p.DureeReservation DAY) as DateFinRes
    FROM lignereservation lr
    inner join reservation r on lr.NumReservation = r.NumReservation
    inner join produit p on lr.CodeProduit = p.CodeProduit
    where lr.IdBoutique = ?
    and lr.StatusLigneRes = "non traite"
    and DATE_ADD(DateReservation, INTERVAL p.DureeReservation DAY) < CURRENT_TIMESTAMP()
    order by DateReservation asc
  */
  public function getResExpired($id){
    $this->load->database();
    return $this->db->select('NumLigneRes,lr.NumReservation,DateReservation,StatusLigneRes,QteLigneRes,LibelleProduit,DATE_ADD(DateReservation, INTERVAL p.DureeReservation DAY) as DateFinRes')
                    ->from('lignereservation as lr')
                    ->join('reservation as r', 'lr.NumReservation = r.NumReservation')
                    ->join('produit as p', 'lr.CodeProduit = p.CodeProduit')
                    ->where('lr.IdBoutique', $id)
                    ->where('lr.StatusLigneRes', 'non traite')
                    ->where('DATE_ADD(DateReservation, INTERVAL p.DureeReservation DAY) <', date("Y-m-d H:i:s"))
                    ->order_by('DateReservation', 'ASC')
                    ->get()
                    ->result();
  }

  /*
  Retourne les champs d'une reservation expire pour suppression

  SELECT NumLigneRes, lr.NumReservation, lr.CodeProduit
    FROM lignereservation lr
    inner join reservation  r on lr.NumReservation = r.NumReservation
    inner join produit p on lr.CodeProduit = p.CodeProduit
    where lr.IdBoutique = 11
    and lr.StatusLigneRes = "non traite"
    and DATE_ADD(DateReservation, INTERVAL p.DureeReservation DAY) < CURRENT_TIMESTAMP()

  */
  public function getResForDelete($id){
    $this->load->database();
    return $this->db->select('NumLigneRes, lr.NumReservation, lr.CodeProduit,lr.NumReservation')
                    ->from('lignereservation as lr')
                    ->join('reservation as r', 'lr.NumReservation = r.NumReservation')
                    ->join('produit as p', 'lr.CodeProduit = p.CodeProduit')
                    ->where('lr.IdBoutique', $id)
                    ->where('lr.StatusLigneRes', 'non traite')
                    ->where('DATE_ADD(DateReservation, INTERVAL p.DureeReservation DAY) <', date("Y-m-d H:i:s"))
                    ->get()
                    ->result();
  }

  /*
    Supprime la reservation donnee de la liste pour une boutique
  */
  public function deleteForNum($id,$num,$numCom){
    $this->load->database();
    return $this->db->where('NumLigneRes',$num)
                    ->where('NumReservation',$numCom)
                    ->where('IdBoutique',$id)
                    ->delete($this->table);
  }

  /*
  Met a jour le status d'une reservation pour une boutique donnee
  */
  public function updateStatus($data){
    $this->load->database();
    return $this->db->set('StatusLigneRes', $data['StatusLigneRes'])
                    ->where('NumLigneRes', $data['NumLigneRes'])
                    ->where('NumReservation', $data['NumReservation'])
                    ->where('idBoutique', $data['idBoutique'])
                    ->update($this->table);
  }

    public function insertLigneReservation($numResa, $numLigne, $qte, $shop, $numProd) {
        $this->load->database();
        return $this->db->set('NumReservation', $numResa)
            ->set('StatusLigneRes', "non traite")
            ->set('qteLigneRes', $qte)
            ->set('NumLigneRes', $numLigne)
            ->set('idBoutique', $shop)
            ->set('CodeProduit', $numProd)
            ->insert($this->table);
    }

    public function total($id){
      $this->load->database();
      return $this->db->select('COUNT(NumLigneRes) as total')
                    ->from($this->table)
                    ->where('IdBoutique', $id)
                    ->get()
                    ->result();
    }

    public function totalLivred($id){
      $this->load->database();
      return $this->db->select('COUNT(NumLigneRes) as total')
                    ->from($this->table)
                    ->where('IdBoutique', $id)
                    ->where('StatusLigneRes', 'traite')
                    ->get()
                    ->result();
    }

    /*
      Select COUNT(NumLigneRes) as total,DATE_ADD(DateReservation, INTERVAL p.DureeReservation DAY) as DateFinRes
      FROM lignereservation lr
      inner join produit p on lr.CodeProduit = p.CodeProduit
      where lr.IdBoutique = ?
      and lr.StatusLigneRes = "non traite"
      and DATE_ADD(DateReservation, INTERVAL p.DureeReservation DAY) >= CURRENT_TIMESTAMP()
    */
    public function totalEncours($id){
      $this->load->database();
      return $this->db->select('COUNT(NumLigneRes) as total')
                    ->from('lignereservation as lr')
                    ->join('produit as p', 'lr.CodeProduit = p.CodeProduit')
                    ->join('reservation as r', 'lr.NumReservation = r.NumReservation')
                    ->where('lr.IdBoutique', $id)
                    ->where('lr.StatusLigneRes', 'non traite')
                    ->where('DATE_ADD(DateReservation, INTERVAL p.DureeReservation DAY) >=', date("Y-m-d H:i:s"))
                    ->where('StatusLigneRes', 'traite')
                    ->get()
                    ->result();
    }



}

?>
