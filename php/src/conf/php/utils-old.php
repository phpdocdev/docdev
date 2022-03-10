<?
class Utils{
function chooseState($name, $selected, $js){

  if(!$selected){
    $selected = 'AR';
  }

  $States = array(
    AK => 'Alaska',
    AL => 'Alabama',
    AR => 'Arkansas',
    AZ => 'Arizona',
    CA => 'California',
    CO => 'Colorado',
    CT => 'Connecticut',
    DC => 'District of Columbia',
    DE => 'Delaware',
    FL => 'Florida',
    GA => 'Georgia',
    HI => 'Hawaii',
    IA => 'Iowa',
    ID => 'Idaho',
    IL => 'Illinois',
    IN => 'Indiana',
    KS => 'Kansas',
    KY => 'Kentucky',
    LA => 'Louisiana',
    MA => 'Massachusetts',
    MD => 'Maryland',
    ME => 'Maine',
    MI => 'Michigan',
    MN => 'Minnesota',
    MO => 'Missouri',
    MS => 'Mississippi',
    MT => 'Montana',
    NC => 'North Carolina',
    ND => 'North Dakota',
    NE => 'Nebraska',
    NH => 'New Hampshire',
    NJ => 'New Jersey',
    NM => 'New Mexico',
    NV => 'Nevada',
    NY => 'New York',
    OH => 'Ohio',
    OK => 'Oklahoma',
    'OR' => 'Oregon',
    PA => 'Pennsylvania',
    RI => 'Rhode Island',
    SC => 'South Carolina',
    SD => 'South Dakota',
    TN => 'Tennessee',
    TX => 'Texas',
    UT => 'Utah',
    VA => 'Virginia',
    VT => 'Vermont',
    WA => 'Washington',
    WI => 'Wisconsin',
    WV => 'West Virginia',
    WY => 'Wyoming'
  );

  $ret = '<select name="'.$name.'" '.$js.'>';

  while(list($ab,$nm) = each($States)){
    if($selected == $ab){
      $sel = 'selected';
    }else{
      $sel = '';
    }
    $ret .= '<option '.$sel.' value="'.$ab.'">'.$nm.'</option>';
  }
  
  return $ret . '</select>';

}
function chooseCounty($name, $selected, $js){
// if(!$selected){
//    $selected = 'Bradley';
//  }
  $Counties = array(
0  =>  '',
1  => 'Arkansas',
2  => 'Ashley',       
3  => 'Baxter',       
4  => 'Benton',       
5  => 'Boone',        
6  => 'Bradley',     
7  => 'Calhoun',      
8  => 'Carroll',      
9  => 'Chicot',       
10 => 'Clark',        
11 => 'Clay',         
12 => 'Cleburne',     
13 => 'Cleveland',    
14 => 'Columbia',     
15 => 'Conway',       
16 => 'Craighead',    
17 => 'Crawford',     
18 => 'Crittenden',   
19 => 'Cross',        
20 => 'Dallas',       
21 => 'Desha',        
22 => 'Drew',         
23 => 'Faulkner',     
24 => 'Franklin',     
25 => 'Fulton',       
26 => 'Garland',      
27 => 'Grant',       
28 => 'Greene',       
29 => 'Hempstead',    
30 => 'Hot Spring',   
31 => 'Howard',       
32 => 'Independence', 
33 => 'Izard',        
34 => 'Jackson',      
35 => 'Jefferson',    
36 => 'Johnson',      
37 => 'Lafayette',    
38 => 'Lawrence',     
39 => 'Lee',          
40 => 'Lincoln',      
41 => 'Little River',
42 => 'Logan',        
43 => 'Lonoke',       
44 => 'Madison',      
45 => 'Marion',     
46 => 'Miller',       
47 => 'Mississippi',  
48 => 'Monroe',       
49 => 'Montgomery',   
50 => 'Nevada',       
51 => 'Newton',       
52 => 'Ouachita',    
53 => 'Perry',        
54 => 'Phillips',     
55 => 'Pike',         
56 => 'Poinsett',     
57 => 'Polk',         
58 => 'Pope',         
59 => 'Prairie',      
60 => 'Pulaski',      
61 => 'Randolph',     
62 => 'Saline',       
63 => 'Scott',        
64 => 'Searcy',       
65 => 'Sebastian',
66 => 'Sevier',       
67 => 'Sharp',        
68 => 'St Francis',   
69 => 'Stone',        
70 => 'Union',        
71 => 'Van Buren',    
72 => 'Washington',   
73 => 'White',      
74 => 'Woodruff',
75 => 'Yell'
               
  );           
               
  $ret = '<select name="'.$name.'" '.$js.'>';
               
  while(list($ab,$nm) = each($Counties)){
    if($selected == $ab){
      $sel = 'selected';
    }else{     
      $sel = '';
    }          
    $ret .= '<option '.$sel.' value="'.$ab.'">'.$nm.'</option>';
  }           
               
  return $ret . '</select>';
               
}

function choosefullCounty($name, $selected, $js){
// if(!$selected){
//    $selected = 'Bradley';
//  }
  $Counties = array(
0  =>  '',
'Arkansas'  => 'Arkansas',
'Ashley'  => 'Ashley',       
'Baxter'  => 'Baxter',       
'Benton'  => 'Benton',       
'Boone'  => 'Boone',        
'Bradley'  => 'Bradley',     
'Calhoun'  => 'Calhoun',      
'Carroll'  => 'Carroll',      
'Chicot'  => 'Chicot',       
'Clark' => 'Clark',        
'Clay' => 'Clay',         
'Cleburne' => 'Cleburne',     
'Cleveland' => 'Cleveland',    
'Columbia' => 'Columbia',     
'Conway' => 'Conway',       
'Craighead' => 'Craighead',    
'Crawford' => 'Crawford',     
'Crittenden' => 'Crittenden',   
'Cross' => 'Cross',        
'Dallas'=> 'Dallas',       
'Desha' => 'Desha',        
'Drew' => 'Drew',         
'Faulkner' => 'Faulkner',     
'Franklin' => 'Franklin',     
'Fulton' => 'Fulton',       
'Garland' => 'Garland',      
'Grant' => 'Grant',       
'Greene' => 'Greene',       
'Hempstead' => 'Hempstead',    
'Hot Spring' => 'Hot Spring',   
'Howard' => 'Howard',       
'Independence' => 'Independence', 
'Izard' => 'Izard',        
'Jackson' => 'Jackson',      
'Jefferson' => 'Jefferson',    
'Johnson' => 'Johnson',      
'Lafayette' => 'Lafayette',    
'Lawrence' => 'Lawrence',     
'Lee' => 'Lee',          
'Lincoln' => 'Lincoln',      
'Little River' => 'Little River',
'Logan' => 'Logan',        
'Lonoke' => 'Lonoke',       
'Madison' => 'Madison',      
'Marion' => 'Marion',     
'Miller' => 'Miller',       
'Mississippi' => 'Mississippi',  
'Monroe' => 'Monroe',       
'Montgomery' => 'Montgomery',   
'Nevada' => 'Nevada',       
'Newton' => 'Newton',       
'Ouachita' => 'Ouachita',    
'Perry' => 'Perry',        
'Phillips' => 'Phillips',     
'Pike' => 'Pike',         
'Poinsett' => 'Poinsett',     
'Polk' => 'Polk',         
'Pope' => 'Pope',         
'Prairie' => 'Prairie',      
'Pulaski' => 'Pulaski',      
'Randolph' => 'Randolph',     
'Saline' => 'Saline',       
'Scott' => 'Scott',        
'Searcy' => 'Searcy',       
'Sebastian' => 'Sebastian',
'Sevier' => 'Sevier',       
'Sharp' => 'Sharp',        
'St Francis' => 'St Francis',   
'Stone' => 'Stone',        
'Union' => 'Union',        
'Van Buren' => 'Van Buren',    
'Washington' => 'Washington',   
'White' => 'White',      
'Woodruff' => 'Woodruff',
'Woodruff' => 'Yell'
               
  );           
               
  $ret = '<select name="'.$name.'" '.$js.'>';
               
  while(list($ab,$nm) = each($Counties)){
    if($selected == $ab){
      $sel = 'selected';
    }else{     
      $sel = '';
    }          
    $ret .= '<option '.$sel.' value="'.$ab.'">'.$nm.'</option>';
  }           
               
  return $ret . '</select>';
               
}
}           
?>