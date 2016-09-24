delete from dir;
insert into dir (id,name,dirup,dirdn,diff) 
select
      x.id,
			x.name,

      if( 
				x.last < @lastlast, 
				1,0) as dirdn,
      if( 
				x.last > @lastlast, 
				1,0) as dirup,
      @lastlast := x.last as diff
   from
      hist x,
      ( select @lastid := 0,
               @lastlast := 0 ) SQLVars

