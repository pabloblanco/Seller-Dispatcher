<div class="block-plan p-l-0 col-sm-12 row">
@php
  if(!isset($bundle)){
    $bundle='N';
  }
  if(isset($plan_payment) && is_object($plan_payment)){
    $plan=$plan_payment;
  }
@endphp
@if($bundle=='Y')
  {{--Plan bundler--}}

<div class="col-sm-12">
  <label>Descripción del combo:</label>
  <p id="txt-plan-description_">{{$plan->general->description}}</p>
</div>
<hr>
@php
$costoT=0;
$articlesBundle = [
  [ 'type' => 'H',
    'label' => "Hogar",
    'icon'=> 'fa-home',
    'separator' => true],

  [ 'type' => 'T',
    'label' => "Telefonia",
    'icon' => 'fa-mobile',
    'separator' => true],

  [ 'type' => 'M',
    'label' => "Mifi",
    'icon' => 'fa-globe',
    'separator' => true],

  [ 'type' => 'MH',
    'label' => "Mifi Huella",
    'icon' => 'fa-map-o',
    'separator' => true],

  [ 'type' => 'F',
    'label' => "Fibra",
    'icon' => 'fa-wifi',
    'separator' => false]
];
  $category_T = '';
  $descrip_T = '';
@endphp

@foreach ($articlesBundle as $item)
  @php
    $info = "info_".$item['type'];
  @endphp
  @if(isset($plan->$info))
    @php
    $titleView = "Combo ".$item['label'];
    $plan_art = $plan->$info;
    $costoT += $plan_art->total_price;

    if($item['type'] == 'T'){
      //Reviso si es simcard o smarphone
      $category_T = $plan->$info->category_id;
      $descrip_T = $plan->$info->category_title;
    }
    @endphp
    <div class="col-md-12">
      <i aria-hidden="true" class="fa {{$item['icon']}}"></i>
      <label id="title_plan_{{$item['type']}}">{{$titleView}}:</label>
    </div>
    <div class="row px-3">
      @include('fiber.Detailplan', ['plan' => $plan_art, 'titleView' => $titleView, 'typeArt'=> $item['type']])
    </div>
    @if($item['separator'])
      <hr style="width: 80%; height: 1px; color: black;">
    @endif
  @endif
@endforeach
<input type="hidden" id="category_T" name="category_T" value="{{$category_T}}" data-descrip="{{$descrip_T}}">
<div class="col-md-12 pb-3">
  <label><strong id="bundlepay"> Costo total del combo: ${{number_format($costoT, 2, '.', ',')}} </strong></label>
</div>

@else
  {{--Plan normal--}}

  @include('fiber.Detailplan', ['plan' => $plan, 'titleView' => '', 'typeArt'=> ''])
@endif
</div>
