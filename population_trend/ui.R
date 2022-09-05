#
# This is the user-interface definition of a Shiny web application. You can
# run the application by clicking 'Run App' above.
#
# Find out more about building applications with Shiny here:
#
#    http://shiny.rstudio.com/
#

library(shiny)
library(shinythemes)
library(plotly)

# Define UI for application that draws a histogram
shinyUI(fluidPage(id = 'test',
    theme = shinytheme("sandstone"),
    tags$style('#test {
                             background-color: #D1DFB8;
              }'),
    # Application title
    titlePanel("Yearly Sightings of Species"),

    # Sidebar with a slider input for number of bins
    sidebarLayout(
        sidebarPanel(
            selectInput("specie", "Species:", 
                        choices=c('BrownHeaded', 'White-napped', 'New Holland')),
            sliderInput("bins",
                        "Year:",
                        min = 2001,
                        max = 2022,
                        value = 2022, sep =""
        ),
),
        # Show a plot of the generated distribution
        mainPanel(
                plotlyOutput('line'),
                hr()
        )
    
)))

