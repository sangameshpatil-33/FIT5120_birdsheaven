#
# This is the server logic of a Shiny web application. You can run the
# application by clicking 'Run App' above.
#
# Find out more about building applications with Shiny here:
#
#    http://shiny.rstudio.com/
#

library(shiny)
library(ggplot2)
library(dplyr) 
library(tidyverse)

# Define server logic required to draw a histogram
shinyServer(function(input, output) {
    data1 <- read.csv('https://birdheaven.s3.ap-southeast-2.amazonaws.com/brown_headed_yearly.csv', header=T, sep=",") 
    data2 <- read.csv('https://birdheaven.s3.ap-southeast-2.amazonaws.com/new_holland_yearly.csv', header=T, sep=",") 
    data3 <- read.csv('https://birdheaven.s3.ap-southeast-2.amazonaws.com/white_napped_yearly.csv', header=T, sep=",") 
    
    
    
    output$line <- renderPlotly({
        if (input$specie == 'BrownHeaded'){
            
            model <- lm(Surveys~Year, data = (data1 %>% filter(Year > 2019 & Year < 2023)))
            pred_vals <- predict(model, newdata = data.frame(Year = 2023:2024))
            new_data1 <- data1
            new_data1 <- new_data1 %>% add_row(Year = 2023:2024, Surveys = pred_vals)
            new_data1 <- new_data1 %>% filter(Year > 2021)
            
            
            ggplot() +
                geom_line(data = new_data1, aes(x = Year, y = Surveys), color = "red", size = 1) +
                geom_point(data = new_data1, aes(x = Year, y = Surveys), color = "red") +
                xlab('Year') +
                ylab('Sightings') +
                theme(panel.grid.major = element_blank(), panel.grid.minor = element_blank(),
                      panel.background = element_blank(), axis.line = element_line(colour = "black"), 
                      axis.text.x = element_text(angle=45)) +
                scale_x_continuous(breaks=seq(2001,2024,1))
            
        } else if (input$specie == 'New Holland'){
            
            model <- lm(Surveys~Year, data = (data2 %>% filter(Year > 2019 & Year < 2023)))
            pred_vals <- predict(model, newdata = data.frame(Year = 2023:2024))
            new_data2 <- data2
            new_data2 <- new_data2 %>% add_row(Year = 2023:2024, Surveys = pred_vals)
            new_data2 <- new_data2 %>% filter(Year > 2021)
            
            
            ggplot() +
                
                geom_line(data = new_data2, aes(x = Year, y = Surveys), color = "red", size = 1) + 
                geom_point(data = new_data2, aes(x = Year, y = Surveys), color = "red") +
                xlab('Year') +
                ylab('Sightings') +
                theme(panel.grid.major = element_blank(), panel.grid.minor = element_blank(),
                      panel.background = element_blank(), axis.line = element_line(colour = "black"), 
                      axis.text.x = element_text(angle=45)) +
                scale_x_continuous(breaks=seq(2001,2024,1))
            
        } else if (input$specie == 'White-napped'){
            
            model <- lm(Surveys~Year, data = (data3 %>% filter(Year > 2020 & Year < 2023)))
            pred_vals <- predict(model, newdata = data.frame(Year = 2023:2024))
            new_data3 <- data3
            new_data3 <- new_data3 %>% add_row(Year = 2023:2024, Surveys = pred_vals)
            new_data3 <- new_data3 %>% filter(Year > 2021)
            
            
            ggplot() +
                
                geom_line(data = new_data3, aes(x = Year, y = Surveys), color = "red", size = 1) +
                geom_point(data = new_data3, aes(x = Year, y = Surveys), color = "red") +
                xlab('Year') +
                ylab('Sightings') +
                theme(panel.grid.major = element_blank(), panel.grid.minor = element_blank(),
                      panel.background = element_blank(), axis.line = element_line(colour = "black"), 
                      axis.text.x = element_text(angle=45)) +
                scale_x_continuous(breaks=seq(2001,2024,1))
        }
        
        
    })
})
