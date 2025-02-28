
#source("Tabellen_kwantielen_STOWA2019_Deelrapport4_GroterDan720min_Tmax100.R")
rm(list=ls(all=TRUE))

#!/usr/bin/env Rscript	'this start is to support command line arguments
args = commandArgs(trailingOnly=TRUE)

# Load the package required to read JSON files.
#library("rjson")
library("jsonlite")


# test if there two arguments or zero: if not, return an error
if (length(args)==0) {
  #supply default arguments
  #paste("processing script with default arguments")
  wdir <- "c:/Program Files (x86)/PostgreSQL/EnterpriseDB-ApachePHP/apache/www/meteobase/R/"
  gebiedsoppervlak <- 10
} else if (length(args)==1) {
  #paste("processing script with one argument")
  gebiedsoppervlak <- as.double(args[1])
  #paste("Arguments passed:",args[1],sep=" ")
  wdir <- "c:/Program Files (x86)/PostgreSQL/EnterpriseDB-ApachePHP/apache/www/meteobase/R/"
  #paste("Arguments used:", gebiedsoppervlak,sep=" ")
} else if (length(args)==2) {
  #paste("processing script with one argument")
  wdir <- args[1]
  gebiedsoppervlak <- as.double(args[2])
  #paste("Arguments passed:",args[1],args[2],sep=" ")
  #paste("Arguments used:",wdir, gebiedsoppervlak,sep=" ")
} else {
  stop("The argument 'surface area' was not supplied." , call.=FALSE)	
}



#######################################################################################################################################
# GEBRUIKERSINSTELLINGEN

x_series <- c(0.25,0.5,1,2,4,8,12,24,48,96,192,216) # Let op: duren > 24 uur verwerken een andere routine dan <= 12 uur
A_series <- c(gebiedsoppervlak)
T_series_Langbein <- c(0.5,1,2,5,10,20,25,30,50,100)  #lijst met herhalingstijden voor de regenduurlijnen
Aref <- 5.73
#######################################################################################################################################


# Definieer functie voor afronden naar gehele getallen:
roundup <- function(x) trunc(x+0.5)

# Duur in uren.
duur <- c(NA)
duur[1] <- 0.25
duur[2] <- 0.5
duur[3] <- 1
duur[4] <- 2
duur[5] <- 4
duur[6] <- 8
duur[7] <- 12
duur[8] <- 24
duur[9] <- 48
duur[10] <- 96
duur[11] <- 192
duur[12] <- 216


# Gebiedsgrootte in vierkante kilometers.
Area <- c(NA)
Area[1] <- 1*5.73
Area[2] <- 3*3*5.73
Area[3] <- 5*5*5.73
Area[4] <- 7*7*5.73
Area[5] <- 9*9*5.73
Area[6] <- 11*11*5.73
Area[7] <- 13*13*5.73
Area[8] <- 15*15*5.73
Area[9] <- 17*17*5.73


# Maak array met NA waarden voor de 9 keer 12 combinaties van duur en gebiedsgrootte voor elke GEV-parameter afzonderlijk.
loc <- array(NA, c(12,9))
disp <- array(NA, c(12,9))
kappa <- array(NA, c(12,9))


# Laad waarden van geschatte GEV-parameters voor alle 9 keer 12 combinaties van gebiedsgrootte en duur (radarstatistiek).
u <- c(NA)
u <- read.table(paste(wdir,'Tabel_GEV_area_09D.dat', sep=""),skip=1) 
loc[12,] <- u[,3]
disp[12,] <- u[,6]
kappa[12,] <- u[,5]

u <- c(NA)
u <- read.table(paste(wdir,'Tabel_GEV_area_08D.dat', sep=""),skip=1) 
loc[11,] <- u[,3]
disp[11,] <- u[,6]
kappa[11,] <- u[,5]

u <- c(NA)
u <- read.table(paste(wdir,'Tabel_GEV_area_04D.dat', sep=""),skip=1) 
loc[10,] <- u[,3]
disp[10,] <- u[,6]
kappa[10,] <- u[,5]

u <- c(NA)
u <- read.table(paste(wdir,'Tabel_GEV_area_02D.dat',sep=""),skip=1) 
loc[9,] <- u[,3]
disp[9,] <- u[,6]
kappa[9,] <- u[,5]

u <- c(NA)
u <- read.table(paste(wdir,'Tabel_GEV_area_24H.dat',sep=""),skip=1) 
loc[8,] <- u[,3]
disp[8,] <- u[,6]
kappa[8,] <- u[,5]

u <- c(NA)
u <- read.table(paste(wdir,'Tabel_GEV_area_12H.dat', sep=""),skip=1) 
loc[7,] <- u[,3]
disp[7,] <- u[,6]
kappa[7,] <- u[,5]

u <- c(NA)
u <- read.table(paste(wdir,'Tabel_GEV_area_08H.dat', sep=""),skip=1) 
loc[6,] <- u[,3]
disp[6,] <- u[,6]
kappa[6,] <- u[,5]

u <- c(NA)
u <- read.table(paste(wdir,'Tabel_GEV_area_04H.dat', sep=""),skip=1) 
loc[5,] <- u[,3]
disp[5,] <- u[,6]
kappa[5,] <- u[,5]

u <- c(NA)
u <- read.table(paste(wdir,'Tabel_GEV_area_120min.dat', sep=""),skip=1) 
loc[4,] <- u[,3]
disp[4,] <- u[,6]
kappa[4,] <- u[,5]

u <- c(NA)
u <- read.table(paste(wdir,'Tabel_GEV_area_60min.dat', sep=""),skip=1) 
loc[3,] <- u[,3]
disp[3,] <- u[,6]
kappa[3,] <- u[,5]

u <- c(NA)
u <- read.table(paste(wdir,'Tabel_GEV_area_30min.dat',sep=""),skip=1) 
loc[2,] <- u[,3]
disp[2,] <- u[,6]
kappa[2,] <- u[,5]

u <- c(NA)
u <- read.table(paste(wdir,'Tabel_GEV_area_15min.dat',sep=""),skip=1) 
loc[1,] <- u[,3]
disp[1,] <- u[,6]
kappa[1,] <- u[,5]



# Coefficienten van GEV parameters schatten (dus zonder bootstrap samples, maar voor het oorspronkelijke sample).
# Voor D = 0.25 uur de 2 grootste gebiedsgrootten niet meenemen (radarstatistiek):
k = 0
lijst_Duur = c(NA)
lijst_Area = c(NA)
lijst_loc = c(NA)
lijst_disp = c(NA)
lijst_kappa = c(NA)
for (z in 1:9)
{
   s = 0
   for (i in 1:12)
   {
      s = s + 1
      if (duur[i]!=0.25 | (duur[i]==0.25 & (Area[z]!=1289.25 & Area[z]!=1655.97)) )
      {
        k = k + 1
      	lijst_Area[k] = Area[z]
      	lijst_Duur[k] = duur[i]
      	lijst_loc[k] = loc[s,z]
      	lijst_disp[k] = disp[s,z]
      	lijst_kappa[k] = kappa[s,z]
      }
   }
}


# Voor locatieparameter (radarstatistiek).
C <- 82
lijst_Duur2 <- lijst_Duur 
lijst_Duur2[which(lijst_Duur2>C)] <- C
lijst_Duur3 <- lijst_Duur 
lijst_Duur3[which(lijst_Duur3<=C)] <- C
A_loc <- B_loc <- c(NA)
A_loc <- lijst_loc ~ a_coef * lijst_Duur^b_coef + (c_coef + d_coef*log(lijst_Duur2/C)) * lijst_Area^e_coef + d_coef * lijst_Area^g_coef * (lijst_Duur3 - C) + f_coef
B_loc = nls(A_loc, start=list(a_coef=24.412378,b_coef=0.191958,c_coef=-0.698425,d_coef=0.104733,e_coef=0.234975,f_coef=-9.162435,g_coef=-0.009387))


# Voor vormparameter (radarstatistiek).
D1 = 24
lijst_Duur2 = lijst_Duur 
lijst_Duur2[which(lijst_Duur2<D1)] = D1
lijst_Duur3 = lijst_Duur
lijst_Duur3[which(lijst_Duur3>D1)] = D1
A_shp <- lijst_kappa ~ a_coef + b_coef * log(lijst_Area) * ( (log(D1)-log(lijst_Duur3))) + c_coef * (lijst_Duur2 - D1)
B_shp <- nls(A_shp, start=list(a_coef=2,b_coef=0.3,c_coef=0.4))


# Voor dispersiecoefficient (radarstatistiek).
A_disp <- B_disp <- c(NA)
A_disp <- lijst_disp ~ a_coef + b_coef * log(lijst_Duur) + d_coef * log(lijst_Area)
B_disp <- nls(A_disp, start=list(a_coef=2,b_coef=0.3,d_coef=0.52))  


############################################################################################################################################
# PROCESSEN VAN DE GEBRUIKERSINPUT
# Voor alle herhalingstijden de Langbein relatie gebruiken
for (A in A_series)
{
	R <- R_ref <- ARF <- RSTOWA2019_5punt73km2 <- R_niet_afgerond <- array(NA,c(length(T_series_Langbein),length(x_series)))   
	i <- j <- 0
	for (T in T_series_Langbein)
	{
		i <- i + 1
		j <- 0
		for (x in x_series)
      	{
         	j <- j + 1

			# Radarstatistiek berekenen:
      		if (x>=0.5 | (x<0.5 & A<=968.37)) 
			{
				if ( x <= C )
				{
      				locAref <- coef(B_loc)[1] * x^coef(B_loc)[2] + (coef(B_loc)[3] + coef(B_loc)[4] * log(x/C)) * Aref^coef(B_loc)[5] + coef(B_loc)[6]
      				loc <- coef(B_loc)[1] * x^coef(B_loc)[2] + (coef(B_loc)[3] + coef(B_loc)[4] * log(x/C)) * A^coef(B_loc)[5] + coef(B_loc)[6]
				}
				if ( x > C )
				{
       				locAref <- coef(B_loc)[1] * x^coef(B_loc)[2] + coef(B_loc)[3] * Aref^coef(B_loc)[5] + coef(B_loc)[4] * Aref^coef(B_loc)[7] * (x-C) + coef(B_loc)[6]
       				loc <- coef(B_loc)[1] * x^coef(B_loc)[2] + coef(B_loc)[3] * A^coef(B_loc)[5] + coef(B_loc)[4] * A^coef(B_loc)[7] * (x-C) + coef(B_loc)[6]
				}

				dispAref <- coef(B_disp)[1] + coef(B_disp)[2] * log(x) + coef(B_disp)[3] * log(Aref) 
				disp <- coef(B_disp)[1] + coef(B_disp)[2] * log(x) + coef(B_disp)[3] * log(A) 

				if (x < D1)
				{
       				shapeAref <- coef(B_shp)[1] + coef(B_shp)[2] * log(Aref) * ( (log(D1)-log(x))) 
       				shape <- coef(B_shp)[1] + coef(B_shp)[2] * log(A) * ( (log(D1)-log(x))) 
				}
				if (x >= D1)
				{
	        		shapeAref <- coef(B_shp)[1] + coef(B_shp)[3] * (x - D1) 
	        		shape <- coef(B_shp)[1] + coef(B_shp)[3] * (x - D1) 
				}

                if (T <= 50)
				{
       				R_ref[i,j] = locAref * (1 + dispAref *(1-T^(-1*shapeAref)) / shapeAref ) 
       				R[i,j] = loc * (1 + disp *(1-T^(-1*shape)) / shape ) 
				}
				if (T > 50)
				{
					#print("Voor herhalingstijden langer dan 50 jaar worden de ARF's voor een herhalingstijd van 50 jaar gebruikt. Dit vanwege de grote onzekerheid voor herhalingstijden groter dan 50 jaar.")
       				R_ref[i,j] = locAref * (1 + dispAref *(1-50^(-1*shapeAref)) / shapeAref ) 
       				R[i,j] = loc * (1 + disp *(1-50^(-1*shape)) / shape ) 	
				}
				if (T > 100)
				{
					#print("Pas op! De opgegeven herhalingstijd is langer dan 100 jaar. De berekende neerslagstatistieken zijn hierdoor uitermate onzeker.")
				}
			}
			else
			{
				R_ref[i,j] <- c(NA)
				R[i,j] <- c(NA)
			}
			
			# 1. ARF om van puntmeting naar de grootte van een radarvak (5.73 km^2) te gaan (uit literatuur):	
			# 1 - 0.043035 * 5.73^0.373935 * x^-0.289186	
			ARFpunt_5punt73km2 <- 1 - 0.08266513 * x^(-0.289186)

			# 2. ARF uit radar:
			ARF[i,j] <- R[i,j]/R_ref[i,j]  
			
			##################################################################################################################
			# VOOR DE GEMAAKTE KEUZE IN DUUR BEPALEN WE DE PARAMETERWAARDEN VAN DE GEV-KANSVERDELING
			# 3. Kwantiel uit Deelrapport 1 STOWA2019 berekenen:
			##################################################################################################################
			if ( x > 12 & x <= 240 )
			{		
			  #print(paste('Jaarrond aan het processen. Duur=',x,sep=" "))
			      
			  #jaarrondstatistiek voor 12 uur tot 10 dagen. Zie p13 deelrapport I
			  Mu <- 1.02 * (0.239 -0.0250 * log(x))^(1/-0.512)
			  Gamma <- 0.478 -0.0681 * log10(x*60) 
			  Theta <- 0.118 -0.266 * log10(x*60) + 0.0586 * (log10(x*60))^2
			    
			  R_STOWA2019 <- Mu * (1 + Gamma * (1-T^(-1*Theta))/Theta)
			    
			} else if (x <= 12) {
			  
			  #print(paste('Jaarrond aan het processen. Duur=',x,sep=" "))
			  
			  # D: duur in minuten.	
			  D <- x*60

			  # Vormparameter:
			  if ( (D >= 10 & D <= 90) | ( D > 90 & D <= 720 & T <= 120) )
			  {
				Theta <- -0.0336 - 0.264 *log10(D)+0.0636*(log10(D))^2
			  }
			  if ( D > 90 & D <= 720 & T > 120 )
			  {
				Theta <- -0.310 - 0.0544 *log10(D)+0.0288*(log10(D))^2
			  }

			  # Locatieparameter:
			  Mu <- 7.339 + 0.848*log10(D)+2.844*(log10(D))^2

			  # Dispersiecoefficient:
			  if ( D >= 10 & D <= 104 )
			  {
				Gamma <- 0.04704 + 0.1978 * log10(D) - 0.05729*(log10(D))^2
			  }
			  if ( D > 104 & D <= 720 )
			  {
				Gamma <- 0.2801 - 0.0333 * log10(D)
			  }
	
			  Beta <- Gamma * Mu
			  Q <- Mu + Beta/Theta * (1 - ((1-exp(-1/T))/exp(-1/T))^Theta)

			  # Voor herhalingstijden van 121 tot 165 jaar, worden door die aanpassing van de vormparameter de neerslagvolumes bij een bepaalde duur kleiner dan 
			  # bij een herhalingstijd van 120 jaar. Deze inconsistentie kan worden opgelost door in voorkomende gevallen het grootste neerslagvolume te nemen:
			  if ( D > 90 & D <= 720 & T > 120 & T <= 165 )
			  {
				Q120 <- Mu + Beta/Theta * (1 - ((1-exp(-1/120))/exp(-1/120))^Theta) 
				Q <- max(Q,Q120)
			  }

			  # Verhoog regensom met 2% vanwege onderschatting van automatische regenmeters t.o.v. handregenmeters:
			  R_STOWA2019 <- Q * 1.02
				
			} else {
			  #print('Geen geldige duur gekozen. Neerslagduren tot 240 worden ondersteund.')
			}
			################################################################################
			
			# 4. Kwantiel berekenen door STOWA2019 te vermenigvuldigen met ARF van punt naar 5.73 km^2 en met ARF o.b.v. radarstatistiek:
			#R[i,j] <- R_STOWA2019 * ARFpunt_5punt73km2 * ARF[i,j]
			R[i,j] <- ARFpunt_5punt73km2 * ARF[i,j]
			#RSTOWA2019_5punt73km2[i,j] <- R_STOWA2019 * ARFpunt_5punt73km2
			RSTOWA2019_5punt73km2[i,j] <- ARFpunt_5punt73km2
			R_niet_afgerond[i,j] <- R[i,j]
			#R[i,j] <- roundup(R[i,j])

	    }

	}
	

	# Wegschrijven tabellen:		
	if (A==5.73)
	{   	
		write.table(data.frame(cbind(paste("A = ",A,"km^2",sep=""),t(x_series))),
		paste(wdir,"Tabel_",A,"km^2","_STOWA2019_Deelrapport4_GroterDan720min.txt",sep=""),row.names=FALSE,col.names=FALSE)
		write.table(data.frame(cbind(T_series_Langbein,RSTOWA2019_5punt73km2)), 
		paste(wdir,"Tabel_",A,"km^2","_STOWA2019_Deelrapport4_GroterDan720min.txt",sep=""),row.names=FALSE,col.names=FALSE,append=TRUE)

		#dataframe converteren naar JSON
		myJson <- toJSON(data.frame(cbind(T_series_Langbein,RSTOWA2019_5punt73km2)))
		print(myJson)
			
	}

	if (A>5.73)
	{   	
	 	write.table(data.frame(cbind(paste("A = ",A,"km^2",sep=""),t(x_series))),
		paste(wdir,"Tabel_",A,"km^2",".txt",sep=""),row.names=FALSE,col.names=FALSE)
	 	write.table(data.frame(cbind(T_series_Langbein,R_niet_afgerond)), 
		paste(wdir,"Tabel_",A,"km^2",".txt",sep=""),row.names=FALSE,col.names=FALSE,append=TRUE)

	 	#dataframe converteren naar JSON
	 	myJson <- toJSON(data.frame(cbind(T_series_Langbein,R_niet_afgerond)))
		print(myJson)
	 		
	 }


}  

############################################################################################################################################







