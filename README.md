peronal detail -> DONE
hazrat select when select hazrat so show hazrat time -> DONE
then show which type is allow here (online, physical) -> DONE
then according to type show online normal, online urgent, physical normal, physical urgent -> DONE
then complete token book -> DONE

Easy paisa integeration -> NOT
Special clourse with doctor_id set in code -> NOT
token_limits doctor_id sy bind hona ha 

---Admin panel start---
Profile Page -> DONE
date schedule and special closure -> DONE 
astana whatsapp group  
Token type and token category crud DONE

now set master project as updated work



--- DB design
	-- clinic_settings
     There are simple details about clinic
    -- doctors
     Basic details of doctor with password admin panel
    -- doctor_schedules
     here dortor select his/her days for checkup  1 = sunday, 2 = monday, 3 = tuesday like this respectivly 
    -- special_closures
     here we define govt days off, official days off, obligation days off like eid, agent off etc
    -- token_types
     Here we define our tokens types for now 2 type (Online, physical)
    -- token_categories
     here are sub token categories (normal, urgent, online-normal, online-argent)
     we have seperate prices according to categories 300, 200, 500, 100
    -- token_limits
     here we define limits of token like ("physical" "normal" token 10 for sunday/monday/etc, "online" "urgent" token 5 for sunday/monday/etc ) 
     there we apply daily token limits as we need
    -- token_type_restrictions
     here we decide which token type doctor use in day. 
     Eaxmple:
       physical and online both will distribute on monday,
       only physical will distribute on tuesday
       only online will distribute on wednesday  
    -- tokens
     it is our main token book table where we save our patient detail and booking details 


      